<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\FindUserDwsCalcSpecUseCase;

/**
 * {@link \UseCase\User\FindUserDwsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserDwsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\FindUserDwsCalcSpecUseCase
     */
    protected $findUserDwsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\FindUserDwsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindUserDwsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                FindUserDwsCalcSpecUseCase::class,
                fn () => $self->findUserDwsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->findUserDwsCalcSpecUseCase = Mockery::mock(
                FindUserDwsCalcSpecUseCase::class
            );
        });
    }
}
