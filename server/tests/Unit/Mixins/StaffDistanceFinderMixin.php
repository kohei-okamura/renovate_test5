<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\StaffDistanceFinder;
use Mockery;

/**
 * StaffDistanceFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait StaffDistanceFinderMixin
{
    /**
     * @var \Domain\Staff\StaffDistanceFinder|\Mockery\MockInterface
     */
    protected $staffDistanceFinder;

    /**
     * StaffDistanceFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinStaffDistanceFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(StaffDistanceFinder::class, fn () => $self->staffDistanceFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->staffDistanceFinder = Mockery::mock(StaffDistanceFinder::class);
        });
    }
}
