<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\IdentifyUserDwsCalcSpecUseCase;

/**
 * {@link \UseCase\User\IdentifyUserDwsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyUserDwsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\IdentifyUserDwsCalcSpecUseCase
     */
    protected $identifyUserDwsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\IdentifyUserDwsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyUserDwsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyUserDwsCalcSpecUseCase::class,
                fn () => $self->identifyUserDwsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyUserDwsCalcSpecUseCase = Mockery::mock(
                IdentifyUserDwsCalcSpecUseCase::class
            );
        });
    }
}
