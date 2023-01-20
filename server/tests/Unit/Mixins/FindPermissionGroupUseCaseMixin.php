<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Permission\FindPermissionGroupUseCase;

/**
 * FindPermissionGroupUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindPermissionGroupUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Permission\FindPermissionGroupUseCase
     */
    protected $findPermissionGroupUseCase;

    /**
     * FindPermissionGroupUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindPermissionGroupUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindPermissionGroupUseCase::class, fn () => $self->findPermissionGroupUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findPermissionGroupUseCase = Mockery::mock(FindPermissionGroupUseCase::class);
        });
    }
}
