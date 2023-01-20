<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Role\RoleRepository;
use Mockery;

/**
 * RoleRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RoleRepositoryMixin
{
    /**
     * @var \Domain\Role\RoleRepository|\Mockery\MockInterface
     */
    protected $roleRepository;

    /**
     * RoleRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRoleRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RoleRepository::class, fn () => $self->roleRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->roleRepository = Mockery::mock(RoleRepository::class);
        });
    }
}
