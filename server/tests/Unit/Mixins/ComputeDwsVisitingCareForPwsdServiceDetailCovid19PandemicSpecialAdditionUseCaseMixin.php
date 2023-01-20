<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase
     */
    protected $computeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase::class,
                fn () => $self->computeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase = Mockery::mock(
                ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase::class
            );
        });
    }
}
