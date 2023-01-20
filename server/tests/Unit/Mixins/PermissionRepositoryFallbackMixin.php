<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Infrastructure\Permission\PermissionRepositoryFallback;
use Mockery;

/**
 * PermissionRepositoryFallback Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait PermissionRepositoryFallbackMixin
{
    /**
     * @var \Infrastructure\Permission\PermissionRepositoryFallback|\Mockery\MockInterface
     */
    protected $permissionRepositoryFallback;

    /**
     * PermissionRepositoryFallback に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinPermissionRepositoryFallback(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(PermissionRepositoryFallback::class, fn () => $self->permissionRepositoryFallback);
        });
        static::beforeEachSpec(function ($self): void {
            $self->permissionRepositoryFallback = Mockery::mock(PermissionRepositoryFallback::class);
        });
    }
}
