<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\EditUserLtcsCalcSpecUseCase;

/**
 * {@link \UseCase\User\EditUserLtcsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditUserLtcsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\EditUserLtcsCalcSpecUseCase
     */
    protected $editUserLtcsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\EditUserLtcsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditUserLtcsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                EditUserLtcsCalcSpecUseCase::class,
                fn () => $self->editUserLtcsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->editUserLtcsCalcSpecUseCase = Mockery::mock(
                EditUserLtcsCalcSpecUseCase::class
            );
        });
    }
}
