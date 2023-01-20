<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\EditUserDwsCalcSpecUseCase;

/**
 * {@link \UseCase\User\EditUserDwsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditUserDwsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\EditUserDwsCalcSpecUseCase
     */
    protected $editUserDwsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\EditUserDwsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditUserDwsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditUserDwsCalcSpecUseCase::class,
                fn () => $self->editUserDwsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editUserDwsCalcSpecUseCase = Mockery::mock(
                EditUserDwsCalcSpecUseCase::class
            );
        });
    }
}
