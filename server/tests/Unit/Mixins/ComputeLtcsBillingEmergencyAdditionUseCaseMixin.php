<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeLtcsBillingEmergencyAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase
     */
    protected $computeLtcsBillingEmergencyAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeLtcsBillingEmergencyAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeLtcsBillingEmergencyAdditionUseCase::class,
                fn () => $self->computeLtcsBillingEmergencyAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeLtcsBillingEmergencyAdditionUseCase = Mockery::mock(
                ComputeLtcsBillingEmergencyAdditionUseCase::class
            );
        });
    }
}
