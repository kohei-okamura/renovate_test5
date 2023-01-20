<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\CreateUserBillingListUseCase;

/**
 * {@link \UseCase\UserBilling\CreateUserBillingListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserBillingListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\CreateUserBillingListUseCase
     */
    protected $createUserBillingListUseCase;

    /**
     * {@link \UseCase\UserBilling\CreateUserBillingListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateUserBillingListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateUserBillingListUseCase::class,
                fn () => $self->createUserBillingListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createUserBillingListUseCase = Mockery::mock(
                CreateUserBillingListUseCase::class
            );
        });
    }
}
