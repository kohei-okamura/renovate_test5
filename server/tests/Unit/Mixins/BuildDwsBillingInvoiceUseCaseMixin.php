<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingInvoiceUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingInvoiceUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingInvoiceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingInvoiceUseCase
     */
    protected $buildDwsBillingInvoiceUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingInvoiceUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingInvoiceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingInvoiceUseCase::class,
                fn () => $self->buildDwsBillingInvoiceUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingInvoiceUseCase = Mockery::mock(
                BuildDwsBillingInvoiceUseCase::class
            );
        });
    }
}
