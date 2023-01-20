<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingServiceReportRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingServiceReportRepositoryMixin
{
    /** @var \Domain\Billing\DwsBillingServiceReportRepository|\Mockery\MockInterface */
    protected $dwsBillingServiceReportRepository;

    /**
     * DwsBillingServiceReportRepository に関する初期化・終了処理を登録する.
     */
    public static function mixinDwsBillingServiceReportRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingServiceReportRepository::class, fn () => $self->dwsBillingServiceReportRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingServiceReportRepository = Mockery::mock(DwsBillingServiceReportRepository::class);
        });
    }
}
