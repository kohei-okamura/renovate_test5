<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunUpdateUserBillingDepositJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase
     */
    protected $runUpdateUserBillingDepositJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunUpdateUserBillingDepositJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunUpdateUserBillingDepositJobUseCase::class,
                fn () => $self->runUpdateUserBillingDepositJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runUpdateUserBillingDepositJobUseCase = Mockery::mock(
                RunUpdateUserBillingDepositJobUseCase::class
            );
        });
    }
}
