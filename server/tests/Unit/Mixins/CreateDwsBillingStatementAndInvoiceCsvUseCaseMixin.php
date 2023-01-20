<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingStatementAndInvoiceCsvUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase
     */
    protected $createDwsBillingStatementAndInvoiceCsvUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingStatementAndInvoiceCsvUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingStatementAndInvoiceCsvUseCase::class,
                fn () => $self->createDwsBillingStatementAndInvoiceCsvUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingStatementAndInvoiceCsvUseCase = Mockery::mock(
                CreateDwsBillingStatementAndInvoiceCsvUseCase::class
            );
        });
    }
}
