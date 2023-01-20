<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Infrastructure\Permission\PermissionGroupRepositoryFallback;
use Mockery;

/**
 * PermissionGroupRepositoryFallback Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait PermissionGroupRepositoryFallbackMixin
{
    /**
     * @var \Infrastructure\Permission\PermissionGroupRepositoryFallback|\Mockery\MockInterface
     */
    protected $permissionGroupRepositoryFallback;

    /**
     * PermissionGroupRepositoryFallback に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinPermissionGroupRepositoryFallback(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(PermissionGroupRepositoryFallback::class, fn () => $self->permissionGroupRepositoryFallback);
        });
        static::beforeEachSpec(function ($self): void {
            $self->permissionGroupRepositoryFallback = Mockery::mock(PermissionGroupRepositoryFallback::class);
        });
    }
}
