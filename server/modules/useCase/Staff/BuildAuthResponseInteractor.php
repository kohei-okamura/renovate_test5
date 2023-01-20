<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Role\Role;
use Domain\Staff\Staff;
use UseCase\Role\LookupRoleUseCase;

/**
 * Auth Response 組み立てユースケース 実装.
 */
class BuildAuthResponseInteractor implements BuildAuthResponseUseCase
{
    private AggregatePermissionCodeListUseCase $aggregateUseCase;
    private LookupRoleUseCase $lookupRoleUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Staff\AggregatePermissionCodeListUseCase $aggregateUseCase
     * @param \UseCase\Role\LookupRoleUseCase $lookupRoleUseCase
     */
    public function __construct(AggregatePermissionCodeListUseCase $aggregateUseCase, LookupRoleUseCase $lookupRoleUseCase)
    {
        $this->aggregateUseCase = $aggregateUseCase;
        $this->lookupRoleUseCase = $lookupRoleUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Staff $staff): array
    {
        $roles = $this->lookupRoleUseCase->handle($context, ...$staff->roleIds);

        return [
            'auth' => [
                'isSystemAdmin' => $roles->exists(fn (Role $x): bool => $x->isSystemAdmin),
                'permissions' => $this->aggregateUseCase->handle($context, $roles),
                'staff' => $staff,
            ],
        ];
    }
}
