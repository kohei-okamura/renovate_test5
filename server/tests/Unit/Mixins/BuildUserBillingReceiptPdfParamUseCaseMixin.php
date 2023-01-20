<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\BuildUserBillingReceiptPdfParamUseCase;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingReceiptPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildUserBillingReceiptPdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\BuildUserBillingReceiptPdfParamUseCase
     */
    protected $buildUserBillingReceiptPdfParamUseCase;

    /**
     * {@link \UseCase\UserBilling\BuildUserBillingReceiptPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildUserBillingReceiptPdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildUserBillingReceiptPdfParamUseCase::class,
                fn () => $self->buildUserBillingReceiptPdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildUserBillingReceiptPdfParamUseCase = Mockery::mock(
                BuildUserBillingReceiptPdfParamUseCase::class
            );
        });
    }
}
