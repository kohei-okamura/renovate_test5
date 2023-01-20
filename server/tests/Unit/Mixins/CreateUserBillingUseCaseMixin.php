<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\CreateUserBillingUseCase;

/**
 * {@link \UseCase\UserBilling\CreateUserBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateUserBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\CreateUserBillingUseCase
     */
    protected $createUserBillingUseCase;

    /**
     * {@link \UseCase\UserBilling\CreateUserBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateUserBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateUserBillingUseCase::class,
                fn () => $self->createUserBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createUserBillingUseCase = Mockery::mock(
                CreateUserBillingUseCase::class
            );
        });
    }
}
