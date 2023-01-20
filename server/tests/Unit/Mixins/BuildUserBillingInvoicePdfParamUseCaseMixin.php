<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\BuildUserBillingInvoicePdfParamUseCase;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingInvoicePdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildUserBillingInvoicePdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\BuildUserBillingInvoicePdfParamUseCase
     */
    protected $buildUserBillingInvoicePdfParamUseCase;

    /**
     * {@link \UseCase\UserBilling\BuildUserBillingInvoicePdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildUserBillingInvoicePdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildUserBillingInvoicePdfParamUseCase::class,
                fn () => $self->buildUserBillingInvoicePdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildUserBillingInvoicePdfParamUseCase = Mockery::mock(
                BuildUserBillingInvoicePdfParamUseCase::class
            );
        });
    }
}
