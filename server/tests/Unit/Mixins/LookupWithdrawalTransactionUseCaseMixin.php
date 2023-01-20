<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\LookupWithdrawalTransactionUseCase;

/**
 * {@link \UseCase\UserBilling\LookupWithdrawalTransactionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupWithdrawalTransactionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\LookupWithdrawalTransactionUseCase
     */
    protected $lookupWithdrawalTransactionUseCase;

    /**
     * {@link \UseCase\UserBilling\LookupWithdrawalTransactionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupWithdrawalTransactionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                LookupWithdrawalTransactionUseCase::class,
                fn () => $self->lookupWithdrawalTransactionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->lookupWithdrawalTransactionUseCase = Mockery::mock(
                LookupWithdrawalTransactionUseCase::class
            );
        });
    }
}
