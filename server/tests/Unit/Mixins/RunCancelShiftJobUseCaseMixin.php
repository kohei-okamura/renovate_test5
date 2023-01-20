<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\RunCancelShiftJobUseCase;

/**
 * RunCancelShiftJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCancelShiftJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\RunCancelShiftJobUseCase
     */
    protected $runCancelShiftJobUseCase;

    /**
     * RunDeleteShiftJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCancelShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunCancelShiftJobUseCase::class, fn () => $self->runCancelShiftJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runCancelShiftJobUseCase = Mockery::mock(RunCancelShiftJobUseCase::class);
        });
    }
}
