<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\BuildUserBillingNoticePdfParamUseCase;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingNoticePdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildUserBillingNoticePdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\BuildUserBillingNoticePdfParamUseCase
     */
    protected $buildUserBillingNoticePdfParamUseCase;

    /**
     * {@link \UseCase\UserBilling\BuildUserBillingNoticePdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildUserBillingNoticePdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildUserBillingNoticePdfParamUseCase::class,
                fn () => $self->buildUserBillingNoticePdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildUserBillingNoticePdfParamUseCase = Mockery::mock(
                BuildUserBillingNoticePdfParamUseCase::class
            );
        });
    }
}
