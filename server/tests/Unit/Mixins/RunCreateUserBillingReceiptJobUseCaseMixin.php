<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunCreateUserBillingReceiptJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateUserBillingReceiptJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateUserBillingReceiptJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunCreateUserBillingReceiptJobUseCase
     */
    protected $runCreateUserBillingReceiptJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunCreateUserBillingReceiptJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateUserBillingReceiptJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateUserBillingReceiptJobUseCase::class,
                fn () => $self->runCreateUserBillingReceiptJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateUserBillingReceiptJobUseCase = Mockery::mock(
                RunCreateUserBillingReceiptJobUseCase::class
            );
        });
    }
}
