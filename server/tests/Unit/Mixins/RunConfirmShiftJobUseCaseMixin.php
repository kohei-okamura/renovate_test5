<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\RunConfirmShiftJobUseCase;

/**
 * RunConfirmShiftJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunConfirmShiftJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\RunConfirmShiftJobUseCase
     */
    protected $runConfirmShiftJobUseCase;

    /**
     * RunConfirmShiftJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(RunConfirmShiftJobUseCase::class, fn () => $self->runConfirmShiftJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->runConfirmShiftJobUseCase = Mockery::mock(RunConfirmShiftJobUseCase::class);
        });
    }
}
