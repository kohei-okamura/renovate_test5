<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsBillingInvoiceListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase
     */
    protected $updateLtcsBillingInvoiceListUseCase;

    /**
     * {@link \UseCase\Billing\UpdateLtcsBillingInvoiceListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsBillingInvoiceListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateLtcsBillingInvoiceListUseCase::class,
                fn () => $self->updateLtcsBillingInvoiceListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateLtcsBillingInvoiceListUseCase = Mockery::mock(
                UpdateLtcsBillingInvoiceListUseCase::class
            );
        });
    }
}
