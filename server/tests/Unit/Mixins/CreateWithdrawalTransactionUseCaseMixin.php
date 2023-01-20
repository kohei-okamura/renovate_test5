<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\CreateWithdrawalTransactionUseCase;

/**
 * {@link \UseCase\UserBilling\CreateWithdrawalTransactionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateWithdrawalTransactionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\CreateWithdrawalTransactionUseCase
     */
    protected $createWithdrawalTransactionUseCase;

    /**
     * {@link \UseCase\UserBilling\CreateWithdrawalTransactionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateWithdrawalTransactionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateWithdrawalTransactionUseCase::class,
                fn () => $self->createWithdrawalTransactionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createWithdrawalTransactionUseCase = Mockery::mock(
                CreateWithdrawalTransactionUseCase::class
            );
        });
    }
}
