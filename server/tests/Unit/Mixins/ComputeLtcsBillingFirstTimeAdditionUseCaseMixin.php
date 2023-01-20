<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeLtcsBillingFirstTimeAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase
     */
    protected $computeLtcsBillingFirstTimeAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeLtcsBillingFirstTimeAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeLtcsBillingFirstTimeAdditionUseCase::class,
                fn () => $self->computeLtcsBillingFirstTimeAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeLtcsBillingFirstTimeAdditionUseCase = Mockery::mock(
                ComputeLtcsBillingFirstTimeAdditionUseCase::class
            );
        });
    }
}
