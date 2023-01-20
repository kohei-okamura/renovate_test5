<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\DeleteUserBillingDepositUseCase;

/**
 * DeleteDepositUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteUserBillingDepositUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\DeleteUserBillingDepositUseCase
     */
    protected $deleteUserBillingDepositUseCase;

    /**
     * DeleteDepositUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDeleteDepositUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteUserBillingDepositUseCase::class, fn () => $self->deleteUserBillingDepositUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteUserBillingDepositUseCase = Mockery::mock(DeleteUserBillingDepositUseCase::class);
        });
    }
}
