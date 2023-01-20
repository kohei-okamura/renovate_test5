<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Shift\AttendanceRepository;
use Mockery;

/**
 * AttendanceRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait AttendanceRepositoryMixin
{
    /**
     * @var \Domain\Shift\AttendanceRepository|\Mockery\MockInterface
     */
    protected $attendanceRepository;

    public static function mixinAttendanceRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(AttendanceRepository::class, fn () => $self->attendanceRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->attendanceRepository = Mockery::mock(AttendanceRepository::class);
        });
    }
}
