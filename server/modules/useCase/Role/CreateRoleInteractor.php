<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Role\Role;
use Domain\Role\RoleRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * ロール登録実装.
 */
final class CreateRoleInteractor implements CreateRoleUseCase
{
    use Logging;

    private RoleRepository $roleRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Role\RoleRepository $roleRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        RoleRepository $roleRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->roleRepository = $roleRepository;
        $this->transaction = $transactionManagerFactory->factory($roleRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Role $role): Role
    {
        $x = $this->transaction->run(function () use ($role, $context): Role {
            $updates = [
                'organizationId' => $context->organization->id,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            return $this->roleRepository->store($role->copy($updates));
        });
        $this->logger()->info(
            '権限情報が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
