<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\ConfirmAttendanceUseCase;

/**
 * ConfirmAttendanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ConfirmAttendanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\ConfirmAttendanceUseCase
     */
    protected $confirmAttendanceUseCase;

    /**
     * ConfirmAttendanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ConfirmAttendanceUseCase::class, fn () => $self->confirmAttendanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->confirmAttendanceUseCase = Mockery::mock(ConfirmAttendanceUseCase::class);
        });
    }
}
