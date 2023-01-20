<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase;

/**
 * {@link \UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetHomeVisitLongTermCareCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase
     */
    protected $getHomeVisitLongTermCareCalcSpecUseCase;

    /**
     * {@link \UseCase\Office\GetHomeVisitLongTermCareCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetHomeVisitLongTermCareCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetHomeVisitLongTermCareCalcSpecUseCase::class,
                fn () => $self->getHomeVisitLongTermCareCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getHomeVisitLongTermCareCalcSpecUseCase = Mockery::mock(
                GetHomeVisitLongTermCareCalcSpecUseCase::class
            );
        });
    }
}
