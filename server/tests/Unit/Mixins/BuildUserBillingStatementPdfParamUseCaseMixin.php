<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildUserBillingStatementPdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase
     */
    protected $buildUserBillingStatementPdfParamUseCase;

    /**
     * {@link \UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildUserBillingStatementPdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildUserBillingStatementPdfParamUseCase::class,
                fn () => $self->buildUserBillingStatementPdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildUserBillingStatementPdfParamUseCase = Mockery::mock(
                BuildUserBillingStatementPdfParamUseCase::class
            );
        });
    }
}
