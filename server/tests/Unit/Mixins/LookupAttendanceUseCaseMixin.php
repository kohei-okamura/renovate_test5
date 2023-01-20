<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\LookupAttendanceUseCase;

/**
 * LookupAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\LookupAttendanceUseCase
     */
    protected $lookupAttendanceUseCase;

    /**
     * LookupAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupAttendanceUseCase::class, fn () => $self->lookupAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupAttendanceUseCase = Mockery::mock(LookupAttendanceUseCase::class);
        });
    }
}
