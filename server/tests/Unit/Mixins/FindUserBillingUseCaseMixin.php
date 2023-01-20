<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\UserBilling\FindUserBillingUseCase;

/**
 * FindUserBillingUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\UserBilling\FindUserBillingUseCase
     */
    protected $findUserBillingUseCase;

    /**
     * FindUserBillingUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindUserBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindUserBillingUseCase::class, fn () => $self->findUserBillingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findUserBillingUseCase = Mockery::mock(FindUserBillingUseCase::class);
        });
    }
}
