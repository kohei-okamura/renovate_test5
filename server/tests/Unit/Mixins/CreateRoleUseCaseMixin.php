<?php

/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Role\CreateRoleUseCase;

/**
 * CreateRoleUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateRoleUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Role\CreateRoleUseCase
     */
    protected $createRoleUseCase;

    /**
     * CreateRoleUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateRoleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateRoleUseCase::class, fn () => $self->createRoleUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createRoleUseCase = Mockery::mock(CreateRoleUseCase::class);
        });
    }
}
