<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Role\RoleRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * ロール削除実装.
 */
final class DeleteRoleInteractor implements DeleteRoleUseCase
{
    use Logging;

    private LookupRoleUseCase $lookupUseCase;
    private RoleRepository $roleRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Role\LookupRoleUseCase $lookupUseCase
     * @param \Domain\Role\RoleRepository $roleRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupRoleUseCase $lookupUseCase,
        RoleRepository $roleRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->roleRepository = $roleRepository;
        $this->transaction = $transactionManagerFactory->factory($roleRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): void
    {
        $this->lookupUseCase->handle($context, $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Role[{$id}] not found");
        });
        $this->transaction->run(function () use ($id) {
            $this->roleRepository->removeById($id);
        });
        $this->logger()->info(
            '権限情報が削除されました',
            ['id' => $id] + $context->logContext()
        );
    }
}
