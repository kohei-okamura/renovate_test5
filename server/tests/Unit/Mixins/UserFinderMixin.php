<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserFinder;
use Mockery;

/**
 * UserFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserFinderMixin
{
    /**
     * @var \Domain\User\UserFinder|\Mockery\MockInterface
     */
    protected $userFinder;

    /**
     * UserFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UserFinder::class, fn () => $self->userFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->userFinder = Mockery::mock(UserFinder::class);
        });
    }
}
