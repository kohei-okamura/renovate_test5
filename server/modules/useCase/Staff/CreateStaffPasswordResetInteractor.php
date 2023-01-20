<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Staff\CreateStaffPasswordResetEvent;
use Domain\Staff\Staff;
use Domain\Staff\StaffPasswordReset;
use Domain\Staff\StaffPasswordResetRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * スタッフパスワード再設定登録実装.
 */
final class CreateStaffPasswordResetInteractor implements CreateStaffPasswordResetUseCase
{
    use Logging;
    use UniqueTokenSupport;

    private const MAX_RETRY_COUNT = 100;
    private const TOKEN_LENGTH = 60;

    private Config $config;
    private EventDispatcher $eventDispatcher;
    private IdentifyStaffByEmailUseCase $identifyStaffByEmailUseCase;
    private StaffPasswordResetRepository $passwordResetRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Staff\CreateStaffPasswordResetInteractor} Constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Staff\IdentifyStaffByEmailUseCase $identifyStaffByEmailUseCase
     * @param \Domain\Staff\StaffPasswordResetRepository $passwordResetRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \Domain\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        Config $config,
        IdentifyStaffByEmailUseCase $identifyStaffByEmailUseCase,
        StaffPasswordResetRepository $passwordResetRepository,
        TransactionManagerFactory $transactionManagerFactory,
        TokenMaker $tokenMaker,
        EventDispatcher $eventDispatcher
    ) {
        $this->identifyStaffByEmailUseCase = $identifyStaffByEmailUseCase;
        $this->passwordResetRepository = $passwordResetRepository;
        $this->tokenMaker = $tokenMaker;
        $this->transaction = $transactionManagerFactory->factory(
            $passwordResetRepository
        );
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $email): void
    {
        $option = $this->transaction->run(function () use ($email, $context) {
            return $this->identifyStaffByEmailUseCase
                ->handle($context, $email)
                ->map(function (Staff $staff) {
                    $lifetimeMinutes = $this->config->get('zinger.password_reset.lifetime_minutes');
                    $now = Carbon::now()->startOfMinute();
                    return $this->passwordResetRepository->store(
                        StaffPasswordReset::create([
                            'staffId' => $staff->id,
                            'name' => $staff->name,
                            'email' => $staff->email,
                            'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
                            'expiredAt' => $now->addMinutes($lifetimeMinutes),
                            'createdAt' => $now,
                        ])
                    );
                });
        });
        $option->each(function (StaffPasswordReset $x) use ($context): void {
            $this->logger()->info(
                'スタッフパスワード再設定が登録されました',
                ['id' => $x->id] + $context->logContext()
            );
            $this->eventDispatcher->dispatch(new CreateStaffPasswordResetEvent($context, $x));
        });
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        return $this->passwordResetRepository->lookupOptionByToken($token)->isEmpty();
    }
}
