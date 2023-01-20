<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsBillingInvoiceRecordListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase
     */
    protected $buildLtcsBillingInvoiceRecordListUseCase;

    /**
     * {@link \UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildLtcsBillingInvoiceRecordListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsBillingInvoiceRecordListUseCase::class,
                fn () => $self->buildLtcsBillingInvoiceRecordListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsBillingInvoiceRecordListUseCase = Mockery::mock(
                BuildLtcsBillingInvoiceRecordListUseCase::class
            );
        });
    }
}
