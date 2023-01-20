<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateWithdrawalTransactionFileJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase
     */
    protected $runCreateWithdrawalTransactionFileJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateWithdrawalTransactionFileJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateWithdrawalTransactionFileJobUseCase::class,
                fn () => $self->runCreateWithdrawalTransactionFileJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateWithdrawalTransactionFileJobUseCase = Mockery::mock(
                RunCreateWithdrawalTransactionFileJobUseCase::class
            );
        });
    }
}
