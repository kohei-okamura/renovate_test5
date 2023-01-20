<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\CancelShiftUseCase;

/**
 * CancelShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CancelShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\CancelShiftUseCase
     */
    protected $cancelShiftUseCase;

    /**
     * CancelShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCancelShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CancelShiftUseCase::class, fn () => $self->cancelShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->cancelShiftUseCase = Mockery::mock(CancelShiftUseCase::class);
        });
    }
}
