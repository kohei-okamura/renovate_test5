<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\UserBilling\UserBillingFinder;
use Mockery;

/**
 * UserBillingFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserBillingFinderMixin
{
    /**
     * @var \Domain\UserBilling\UserBillingFinder|\Mockery\MockInterface
     */
    protected $userBillingFinder;

    /**
     * UserBillingFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserBillingFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserBillingFinder::class, fn () => $self->userBillingFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userBillingFinder = Mockery::mock(UserBillingFinder::class);
        });
    }
}
