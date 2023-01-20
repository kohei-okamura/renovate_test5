<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase;

/**
 * RunDeleteDepositJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunDeleteUserBillingDepositJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase
     */
    protected $runDeleteUserBillingDepositJobUseCase;

    /**
     * RunDeleteShiftJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunDeleteDepositUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunDeleteUserBillingDepositJobUseCase::class, fn () => $self->runDeleteUserBillingDepositJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runDeleteUserBillingDepositJobUseCase = Mockery::mock(RunDeleteUserBillingDepositJobUseCase::class);
        });
    }
}
