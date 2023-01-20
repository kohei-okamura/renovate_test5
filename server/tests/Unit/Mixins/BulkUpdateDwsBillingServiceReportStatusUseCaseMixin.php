<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase;

/**
 * {@link \UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BulkUpdateDwsBillingServiceReportStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase
     */
    protected $bulkUpdateDwsBillingServiceReportStatusUseCase;

    /**
     * {@link \UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBulkUpdateDwsBillingServiceReportStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BulkUpdateDwsBillingServiceReportStatusUseCase::class,
                fn () => $self->bulkUpdateDwsBillingServiceReportStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->bulkUpdateDwsBillingServiceReportStatusUseCase = Mockery::mock(
                BulkUpdateDwsBillingServiceReportStatusUseCase::class
            );
        });
    }
}
