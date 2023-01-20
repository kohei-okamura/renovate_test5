<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\FindAttendanceUseCase;

/**
 * FindAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\FindAttendanceUseCase
     */
    protected $findAttendanceUseCase;

    /**
     * FindAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindAttendanceUseCase::class, fn () => $self->findAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findAttendanceUseCase = Mockery::mock(FindAttendanceUseCase::class);
        });
    }
}
