<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeLtcsBillingBaseIncreaseSupportAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase
     */
    protected $computeLtcsBillingBaseIncreaseSupportAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeLtcsBillingBaseIncreaseSupportAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase::class,
                fn () => $self->computeLtcsBillingBaseIncreaseSupportAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeLtcsBillingBaseIncreaseSupportAdditionUseCase = Mockery::mock(
                ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase::class
            );
        });
    }
}
