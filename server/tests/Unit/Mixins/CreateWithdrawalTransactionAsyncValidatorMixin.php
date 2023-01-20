<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Validator\CreateWithdrawalTransactionAsyncValidator;
use Mockery;

/**
 * {@link \Domain\Validator\CreateWithdrawalTransactionAsyncValidator} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateWithdrawalTransactionAsyncValidatorMixin
{
    /**
     * @var \Domain\Validator\CreateWithdrawalTransactionAsyncValidator|\Mockery\MockInterface
     */
    protected $createWithdrawalTransactionAsyncValidator;

    /**
     * {@link \Domain\Validator\CreateWithdrawalTransactionAsyncValidator} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateWithdrawalTransactionAsyncValidator(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateWithdrawalTransactionAsyncValidator::class,
                fn () => $self->createWithdrawalTransactionAsyncValidator
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createWithdrawalTransactionAsyncValidator = Mockery::mock(
                CreateWithdrawalTransactionAsyncValidator::class
            );
        });
    }
}
