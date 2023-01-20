<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\LtcsBillingInvoiceRepository;
use Mockery;

/**
 * {@link \Domain\Billing\LtcsBillingInvoiceRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsBillingInvoiceRepositoryMixin
{
    /**
     * @var \Domain\Billing\LtcsBillingInvoiceRepository|\Mockery\MockInterface
     */
    protected $ltcsBillingInvoiceRepository;

    /**
     * {@link \Domain\Billing\LtcsBillingInvoiceRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinLtcsBillingInvoiceRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LtcsBillingInvoiceRepository::class,
                fn () => $self->ltcsBillingInvoiceRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsBillingInvoiceRepository = Mockery::mock(
                LtcsBillingInvoiceRepository::class
            );
        });
    }
}
