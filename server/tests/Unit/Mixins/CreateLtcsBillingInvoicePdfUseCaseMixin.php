<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateLtcsBillingInvoicePdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase
     */
    protected $createLtcsBillingInvoicePdfUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoicePdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateLtcsBillingInvoicePdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateLtcsBillingInvoicePdfUseCase::class,
                fn () => $self->createLtcsBillingInvoicePdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createLtcsBillingInvoicePdfUseCase = Mockery::mock(
                CreateLtcsBillingInvoicePdfUseCase::class
            );
        });
    }
}
