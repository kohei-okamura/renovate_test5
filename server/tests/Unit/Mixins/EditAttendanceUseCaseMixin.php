<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\EditAttendanceUseCase;

/**
 * EditAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\EditAttendanceUseCase
     */
    protected $editAttendanceUseCase;

    /**
     * EditAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditAttendanceUseCase::class, fn () => $self->editAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editAttendanceUseCase = Mockery::mock(EditAttendanceUseCase::class);
        });
    }
}
