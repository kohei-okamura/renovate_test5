<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RefreshDwsBillingCopayCoordinationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase
     */
    protected $refreshDwsBillingCopayCoordinationUseCase;

    /**
     * {@link \UseCase\Billing\RefreshDwsBillingCopayCoordinationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRefreshDwsBillingCopayCoordinationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RefreshDwsBillingCopayCoordinationUseCase::class,
                fn () => $self->refreshDwsBillingCopayCoordinationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->refreshDwsBillingCopayCoordinationUseCase = Mockery::mock(
                RefreshDwsBillingCopayCoordinationUseCase::class
            );
        });
    }
}
