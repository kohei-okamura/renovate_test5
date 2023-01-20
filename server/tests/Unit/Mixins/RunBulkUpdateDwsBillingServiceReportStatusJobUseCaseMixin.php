<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase;

/**
 * {@link \UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunBulkUpdateDwsBillingServiceReportStatusJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase
     */
    protected $runBulkUpdateDwsBillingServiceReportStatusJobUseCase;

    /**
     * {@link \UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunBulkUpdateDwsBillingServiceReportStatusJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunBulkUpdateDwsBillingServiceReportStatusJobUseCase::class,
                fn () => $self->runBulkUpdateDwsBillingServiceReportStatusJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runBulkUpdateDwsBillingServiceReportStatusJobUseCase = Mockery::mock(
                RunBulkUpdateDwsBillingServiceReportStatusJobUseCase::class
            );
        });
    }
}
