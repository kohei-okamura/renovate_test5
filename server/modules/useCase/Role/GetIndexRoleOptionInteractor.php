<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Role\Role;
use ScalikePHP\Seq;

/**
 * ロール選択肢一覧取得ユースケース実装.
 */
class GetIndexRoleOptionInteractor implements GetIndexRoleOptionUseCase
{
    private FindRoleUseCase $findRoleUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Role\FindRoleUseCase $findRoleUseCase
     */
    public function __construct(FindRoleUseCase $findRoleUseCase)
    {
        $this->findRoleUseCase = $findRoleUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission): Seq
    {
        return $this->findRoleUseCase->handle(
            $context,
            [],
            ['all' => true]
        )
            ->list
            ->map(fn (Role $role): array => [
                'text' => $role->name,
                'value' => $role->id,
            ]);
    }
}
