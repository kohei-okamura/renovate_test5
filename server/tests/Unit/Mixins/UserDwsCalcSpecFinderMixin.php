<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserDwsCalcSpecFinder;
use Mockery;

/**
 * {@link \Domain\User\UserDwsCalcSpecFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserDwsCalcSpecFinderMixin
{
    /**
     * @var \Domain\User\UserDwsCalcSpecFinder|\Mockery\MockInterface
     */
    protected $userDwsCalcSpecFinder;

    /**
     * {@link \Domain\User\UserDwsCalcSpecFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserDwsCalcSpecFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UserDwsCalcSpecFinder::class,
                fn () => $self->userDwsCalcSpecFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->userDwsCalcSpecFinder = Mockery::mock(
                UserDwsCalcSpecFinder::class
            );
        });
    }
}
