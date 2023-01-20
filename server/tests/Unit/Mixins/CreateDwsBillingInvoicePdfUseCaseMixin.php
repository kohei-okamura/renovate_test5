<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingInvoicePdfUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingInvoicePdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingInvoicePdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingInvoicePdfUseCase
     */
    protected $createDwsBillingInvoicePdfUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingInvoicePdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingInvoicePdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingInvoicePdfUseCase::class,
                fn () => $self->createDwsBillingInvoicePdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingInvoicePdfUseCase = Mockery::mock(
                CreateDwsBillingInvoicePdfUseCase::class
            );
        });
    }
}
