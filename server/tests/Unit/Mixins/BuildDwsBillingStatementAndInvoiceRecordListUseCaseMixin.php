<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingStatementAndInvoiceRecordListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase
     */
    protected $buildDwsBillingStatementAndInvoiceRecordListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingStatementAndInvoiceRecordListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingStatementAndInvoiceRecordListUseCase::class,
                fn () => $self->buildDwsBillingStatementAndInvoiceRecordListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingStatementAndInvoiceRecordListUseCase = Mockery::mock(
                BuildDwsBillingStatementAndInvoiceRecordListUseCase::class
            );
        });
    }
}
