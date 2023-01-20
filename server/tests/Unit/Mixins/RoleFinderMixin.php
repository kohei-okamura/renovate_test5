<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Role\RoleFinder;
use Mockery;

/**
 * StaffFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RoleFinderMixin
{
    /**
     * @var \Domain\Role\RoleFinder|\Mockery\MockInterface
     */
    protected $roleFinder;

    /**
     * RoleFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRoleFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RoleFinder::class, fn () => $self->roleFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->roleFinder = Mockery::mock(RoleFinder::class);
        });
    }
}
