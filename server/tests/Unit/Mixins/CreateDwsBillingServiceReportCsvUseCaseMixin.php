<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingServiceReportCsvUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase
     */
    protected $createDwsBillingServiceReportCsvUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingServiceReportCsvUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingServiceReportCsvUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingServiceReportCsvUseCase::class,
                fn () => $self->createDwsBillingServiceReportCsvUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingServiceReportCsvUseCase = Mockery::mock(
                CreateDwsBillingServiceReportCsvUseCase::class
            );
        });
    }
}
