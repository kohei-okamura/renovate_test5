<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Invitation;
use Domain\Staff\InvitationRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 招待編集ユースケース実装.
 */
final class EditInvitationInteractor implements EditInvitationUseCase
{
    use Logging;

    private LookupInvitationUseCase $lookupUseCase;
    private InvitationRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\LookupInvitationUseCase $lookupUseCase
     * @param \Domain\Staff\InvitationRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LookupInvitationUseCase $lookupUseCase,
        InvitationRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): Invitation
    {
        $x = $this->transaction->run(function () use ($context, $id, $values): Invitation {
            $entity = $this->lookupUseCase
                ->handle($context, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("Invitation({$id}) not found");
                });
            return $this->repository->store(
                $entity->copy($values)
            );
        });
        $this->logger()->info(
            '招待が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
