<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateUserBillingStatementJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase
     */
    protected $runCreateUserBillingStatementJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateUserBillingStatementJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateUserBillingStatementJobUseCase::class,
                fn () => $self->runCreateUserBillingStatementJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateUserBillingStatementJobUseCase = Mockery::mock(
                RunCreateUserBillingStatementJobUseCase::class
            );
        });
    }
}
