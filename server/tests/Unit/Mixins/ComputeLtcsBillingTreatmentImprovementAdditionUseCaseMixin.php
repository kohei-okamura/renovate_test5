<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeLtcsBillingTreatmentImprovementAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase
     */
    protected $computeLtcsBillingTreatmentImprovementAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeLtcsBillingTreatmentImprovementAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeLtcsBillingTreatmentImprovementAdditionUseCase::class,
                fn () => $self->computeLtcsBillingTreatmentImprovementAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeLtcsBillingTreatmentImprovementAdditionUseCase = Mockery::mock(
                ComputeLtcsBillingTreatmentImprovementAdditionUseCase::class
            );
        });
    }
}
