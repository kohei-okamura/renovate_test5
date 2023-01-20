<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RefreshDwsBillingServiceReportUseCase;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingServiceReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RefreshDwsBillingServiceReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RefreshDwsBillingServiceReportUseCase
     */
    protected $refreshDwsBillingServiceReportUseCase;

    /**
     * {@link \UseCase\Billing\RefreshDwsBillingServiceReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRefreshDwsBillingServiceReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RefreshDwsBillingServiceReportUseCase::class,
                fn () => $self->refreshDwsBillingServiceReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->refreshDwsBillingServiceReportUseCase = Mockery::mock(
                RefreshDwsBillingServiceReportUseCase::class
            );
        });
    }
}
