<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingInvoiceUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingInvoiceUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingInvoiceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingInvoiceUseCase
     */
    protected $createDwsBillingInvoiceUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingInvoiceUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingInvoiceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingInvoiceUseCase::class,
                fn () => $self->createDwsBillingInvoiceUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingInvoiceUseCase = Mockery::mock(
                CreateDwsBillingInvoiceUseCase::class
            );
        });
    }
}
