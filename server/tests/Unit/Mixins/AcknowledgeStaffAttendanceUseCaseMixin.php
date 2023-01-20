<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\AcknowledgeStaffAttendanceUseCase;

/**
 * AcknowledgeStaffAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait AcknowledgeStaffAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\AcknowledgeStaffAttendanceUseCase
     */
    protected $acknowledgeStaffAttendanceUseCase;

    /**
     * AcknowledgeStaffAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinAcknowledgeStaffAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(AcknowledgeStaffAttendanceUseCase::class, fn () => $self->acknowledgeStaffAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->acknowledgeStaffAttendanceUseCase = Mockery::mock(AcknowledgeStaffAttendanceUseCase::class);
        });
    }
}
