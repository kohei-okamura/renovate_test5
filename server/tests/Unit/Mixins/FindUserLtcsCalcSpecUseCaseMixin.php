<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\FindUserLtcsCalcSpecUseCase;

/**
 * {@link \UseCase\User\FindUserLtcsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserLtcsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\FindUserLtcsCalcSpecUseCase
     */
    protected $findUserLtcsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\FindUserLtcsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindUserLtcsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                FindUserLtcsCalcSpecUseCase::class,
                fn () => $self->findUserLtcsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->findUserLtcsCalcSpecUseCase = Mockery::mock(
                FindUserLtcsCalcSpecUseCase::class
            );
        });
    }
}
