<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\RunConfirmAttendanceJobUseCase;

/**
 * RunConfirmAttendanceJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunConfirmAttendanceJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\RunConfirmAttendanceJobUseCase
     */
    protected $runConfirmAttendanceJobUseCase;

    /**
     * RunConfirmAttendanceJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmAttendanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunConfirmAttendanceJobUseCase::class, fn () => $self->runConfirmAttendanceJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runConfirmAttendanceJobUseCase = Mockery::mock(RunConfirmAttendanceJobUseCase::class);
        });
    }
}
