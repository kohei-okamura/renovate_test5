<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Staff\CreateInvitationEvent;
use Domain\Staff\Invitation;
use Domain\Staff\InvitationRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * 招待登録ユースケース実装.
 */
final class CreateInvitationInteractor implements CreateInvitationUseCase
{
    use Logging;
    use UniqueTokenSupport;

    private const MAX_RETRY_COUNT = 100;
    private const TOKEN_LENGTH = 60;

    private Config $config;
    private EventDispatcher $eventDispatcher;
    private InvitationRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Staff\CreateInvitationInteractor} Constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\Event\EventDispatcher $eventDispatcher
     * @param \Domain\Staff\InvitationRepository $repository
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        Config $config,
        EventDispatcher $eventDispatcher,
        InvitationRepository $repository,
        TokenMaker $tokenMaker,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcher;
        $this->repository = $repository;
        $this->tokenMaker = $tokenMaker;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $invitations): Seq
    {
        $lifetimeMinutes = $this->config->get('zinger.invitation.lifetime_minutes');
        $now = Carbon::now()->startOfMinute();
        /** @var \Domain\Staff\Invitation[]|\ScalikePHP\Seq $xs */
        $xs = $this->transaction->run(fn (): Seq => $invitations->map(fn (Invitation $x): Invitation => $this->repository->store(
            $x->copy([
                'staffId' => null,
                'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
                'expiredAt' => $now->addMinutes($lifetimeMinutes),
                'createdAt' => $now,
            ])
        ))->computed());

        $this->logger()->info(
            '招待が登録されました',
            ['ids' => $xs->map(fn (Invitation $x): int => $x->id)->toArray()] + $context->logContext()
        );
        $xs->each(fn (Invitation $x) => $this->eventDispatcher->dispatch(new CreateInvitationEvent($context, $x, $context->staff)));
        return $xs;
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        return $this->repository->lookupOptionByToken($token)->isEmpty();
    }
}
