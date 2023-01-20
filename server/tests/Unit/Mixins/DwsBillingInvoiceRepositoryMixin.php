<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsBillingInvoiceRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsBillingInvoiceRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsBillingInvoiceRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsBillingInvoiceRepository|\Mockery\MockInterface
     */
    protected $dwsBillingInvoiceRepository;

    /**
     * {@link \Domain\Billing\DwsBillingInvoiceRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinDwsBillingInvoiceRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsBillingInvoiceRepository::class,
                fn () => $self->dwsBillingInvoiceRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsBillingInvoiceRepository = Mockery::mock(
                DwsBillingInvoiceRepository::class
            );
        });
    }
}
