<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateUserBillingInvoiceJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase
     */
    protected $runCreateUserBillingInvoiceJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateUserBillingInvoiceJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateUserBillingInvoiceJobUseCase::class,
                fn () => $self->runCreateUserBillingInvoiceJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateUserBillingInvoiceJobUseCase = Mockery::mock(
                RunCreateUserBillingInvoiceJobUseCase::class
            );
        });
    }
}
