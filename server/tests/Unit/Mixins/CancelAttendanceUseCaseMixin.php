<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\CancelAttendanceUseCase;

/**
 * CancelAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CancelAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\CancelAttendanceUseCase
     */
    protected $cancelAttendanceUseCase;

    /**
     * CancelAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCancelAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CancelAttendanceUseCase::class, fn () => $self->cancelAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->cancelAttendanceUseCase = Mockery::mock(CancelAttendanceUseCase::class);
        });
    }
}
