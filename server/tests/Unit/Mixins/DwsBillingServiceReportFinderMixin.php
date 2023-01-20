<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingServiceReportFinder;
use Mockery;

/**
 * DwsBillingServiceReportFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingServiceReportFinderMixin
{
    /**
     * @var \Domain\Billing\DwsBillingServiceReportFinder|\Mockery\MockInterface
     */
    protected $dwsBillingServiceReportFinder;

    /**
     * {@link \Domain\Billing\DwsBillingServiceReportFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingServiceReportFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingServiceReportFinder::class, fn () => $self->dwsBillingServiceReportFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingServiceReportFinder = Mockery::mock(DwsBillingServiceReportFinder::class);
        });
    }
}
