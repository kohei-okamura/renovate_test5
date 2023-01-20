<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\IdentifyUserLtcsCalcSpecUseCase;

/**
 * {@link \UseCase\User\IdentifyUserLtcsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyUserLtcsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\IdentifyUserLtcsCalcSpecUseCase
     */
    protected $identifyUserLtcsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\IdentifyUserLtcsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyUserLtcsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyUserLtcsCalcSpecUseCase::class,
                fn () => $self->identifyUserLtcsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyUserLtcsCalcSpecUseCase = Mockery::mock(
                IdentifyUserLtcsCalcSpecUseCase::class
            );
        });
    }
}
