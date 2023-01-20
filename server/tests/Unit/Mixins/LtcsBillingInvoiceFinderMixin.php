<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingInvoiceFinder;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingInvoiceFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingInvoiceFinderMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingInvoiceFinder|\Mockery\MockInterface
     */
    protected $ltcsBillingInvoiceFinder;

    /**
     * LtcsBillingInvoiceFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsBillingInvoiceFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(LtcsBillingInvoiceFinder::class, fn () => $self->ltcsBillingInvoiceFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingInvoiceFinder = Mockery::mock(LtcsBillingInvoiceFinder::class);
        });
    }
}
