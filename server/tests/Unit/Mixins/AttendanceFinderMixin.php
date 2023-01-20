<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Shift\AttendanceFinder;
use Mockery;

/**
 * AttendanceFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait AttendanceFinderMixin
{
    /**
     * @var \Domain\Shift\AttendanceFinder|\Mockery\MockInterface
     */
    protected $attendanceFinder;

    /**
     * AttendanceFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinAttendanceFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(AttendanceFinder::class, fn () => $self->attendanceFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->attendanceFinder = Mockery::mock(AttendanceFinder::class);
        });
    }
}
