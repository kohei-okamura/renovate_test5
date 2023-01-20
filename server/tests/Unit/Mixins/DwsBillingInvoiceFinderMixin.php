<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingInvoiceFinder;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingInvoiceFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingInvoiceFinderMixin
{
    /** @var \Domain\Billing\DwsBillingInvoiceFinder|\Mockery\MockInterface */
    protected $dwsBillingInvoiceFinder;

    /**
     * DwsBillingInvoiceFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsBillingInvoiceFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsBillingInvoiceFinder::class, fn () => $self->dwsBillingInvoiceFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsBillingInvoiceFinder = Mockery::mock(DwsBillingInvoiceFinder::class);
        });
    }
}
