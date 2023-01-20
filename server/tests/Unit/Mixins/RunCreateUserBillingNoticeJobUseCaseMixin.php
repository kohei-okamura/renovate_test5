<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateUserBillingNoticeJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase
     */
    protected $runCreateUserBillingNoticeJobUseCase;

    /**
     * {@link \UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateUserBillingNoticeJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateUserBillingNoticeJobUseCase::class,
                fn () => $self->runCreateUserBillingNoticeJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateUserBillingNoticeJobUseCase = Mockery::mock(
                RunCreateUserBillingNoticeJobUseCase::class
            );
        });
    }
}
