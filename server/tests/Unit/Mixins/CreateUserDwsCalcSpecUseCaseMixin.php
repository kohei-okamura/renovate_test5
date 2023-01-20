<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\CreateUserDwsCalcSpecUseCase;

/**
 * {@link \UseCase\User\CreateUserDwsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserDwsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\CreateUserDwsCalcSpecUseCase
     */
    protected $createUserDwsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\CreateUserDwsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateUserDwsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateUserDwsCalcSpecUseCase::class,
                fn () => $self->createUserDwsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createUserDwsCalcSpecUseCase = Mockery::mock(
                CreateUserDwsCalcSpecUseCase::class
            );
        });
    }
}
