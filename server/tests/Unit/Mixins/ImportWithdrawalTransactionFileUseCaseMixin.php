<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase;

/**
 * {@link \UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportWithdrawalTransactionFileUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase
     */
    protected $importWithdrawalTransactionFileUseCase;

    /**
     * {@link \UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportWithdrawalTransactionFileUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ImportWithdrawalTransactionFileUseCase::class,
                fn () => $self->importWithdrawalTransactionFileUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->importWithdrawalTransactionFileUseCase = Mockery::mock(
                ImportWithdrawalTransactionFileUseCase::class
            );
        });
    }
}
