<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateLtcsBillingInvoiceListUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInvoiceListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsBillingInvoiceListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateLtcsBillingInvoiceListUseCase
     */
    protected $createLtcsBillingInvoiceListUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsBillingInvoiceListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateLtcsBillingInvoiceListUseCase::class,
                fn () => $self->createLtcsBillingInvoiceListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createLtcsBillingInvoiceListUseCase = Mockery::mock(
                CreateLtcsBillingInvoiceListUseCase::class
            );
        });
    }
}
