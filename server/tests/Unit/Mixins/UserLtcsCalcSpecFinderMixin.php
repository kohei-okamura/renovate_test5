<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\User\UserLtcsCalcSpecFinder;
use Mockery;

/**
 * {@link \Domain\User\UserLtcsCalcSpecFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UserLtcsCalcSpecFinderMixin
{
    /**
     * @var \Domain\User\UserLtcsCalcSpecFinder|\Mockery\MockInterface
     */
    protected $userLtcsCalcSpecFinder;

    /**
     * {@link \Domain\User\UserLtcsCalcSpecFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUserLtcsCalcSpecFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                UserLtcsCalcSpecFinder::class,
                fn () => $self->userLtcsCalcSpecFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->userLtcsCalcSpecFinder = Mockery::mock(
                UserLtcsCalcSpecFinder::class
            );
        });
    }
}
