<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateDwsBillingInvoiceUseCase;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingInvoiceUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsBillingInvoiceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateDwsBillingInvoiceUseCase
     */
    protected $updateDwsBillingInvoiceUseCase;

    /**
     * {@link \UseCase\Billing\UpdateDwsBillingInvoiceUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsBillingInvoiceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UpdateDwsBillingInvoiceUseCase::class,
                fn () => $self->updateDwsBillingInvoiceUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->updateDwsBillingInvoiceUseCase = Mockery::mock(
                UpdateDwsBillingInvoiceUseCase::class
            );
        });
    }
}
