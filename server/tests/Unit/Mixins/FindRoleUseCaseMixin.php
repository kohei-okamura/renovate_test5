<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Role\FindRoleUseCase;

/**
 * FindRoleUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindRoleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Role\FindRoleUseCase
     */
    protected $findRoleUseCase;

    /**
     * FindRoleUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindRoleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindRoleUseCase::class, fn () => $self->findRoleUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findRoleUseCase = Mockery::mock(FindRoleUseCase::class);
        });
    }
}
