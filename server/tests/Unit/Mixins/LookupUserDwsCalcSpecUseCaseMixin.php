<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\LookupUserDwsCalcSpecUseCase;

/**
 * {@link \UseCase\User\LookupUserDwsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserDwsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\LookupUserDwsCalcSpecUseCase
     */
    protected $lookupUserDwsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\LookupUserDwsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserDwsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupUserDwsCalcSpecUseCase::class,
                fn () => $self->lookupUserDwsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupUserDwsCalcSpecUseCase = Mockery::mock(
                LookupUserDwsCalcSpecUseCase::class
            );
        });
    }
}
