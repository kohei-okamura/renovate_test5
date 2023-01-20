<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\RunImportShiftJobUseCase;

/**
 * RunImportShiftJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunImportShiftJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\RunImportShiftJobUseCase
     */
    protected $runImportShiftJobUseCase;

    /**
     * RunImportShiftJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmResultUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunImportShiftJobUseCase::class, fn () => $self->runImportShiftJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runImportShiftJobUseCase = Mockery::mock(RunImportShiftJobUseCase::class);
        });
    }
}
