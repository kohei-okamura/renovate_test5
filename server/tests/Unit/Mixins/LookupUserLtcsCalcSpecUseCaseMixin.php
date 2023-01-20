<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\LookupUserLtcsCalcSpecUseCase;

/**
 * {@link \UseCase\User\LookupUserLtcsCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupUserLtcsCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\User\LookupUserLtcsCalcSpecUseCase
     */
    protected $lookupUserLtcsCalcSpecUseCase;

    /**
     * {@link \UseCase\User\LookupUserLtcsCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupUserLtcsCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupUserLtcsCalcSpecUseCase::class,
                fn () => $self->lookupUserLtcsCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupUserLtcsCalcSpecUseCase = Mockery::mock(
                LookupUserLtcsCalcSpecUseCase::class
            );
        });
    }
}
