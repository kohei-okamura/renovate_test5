<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsBillingServiceReportStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase
     */
    protected $updateDwsBillingServiceReportStatusUseCase;

    /**
     * {@link \UseCase\Billing\UpdateDwsBillingServiceReportStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsBillingServiceReportStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateDwsBillingServiceReportStatusUseCase::class,
                fn () => $self->updateDwsBillingServiceReportStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateDwsBillingServiceReportStatusUseCase = Mockery::mock(
                UpdateDwsBillingServiceReportStatusUseCase::class
            );
        });
    }
}
