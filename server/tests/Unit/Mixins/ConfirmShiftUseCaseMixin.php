<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\ConfirmShiftUseCase;

/**
 * ConfirmShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ConfirmShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\ConfirmShiftUseCase
     */
    protected $confirmShiftUseCase;

    /**
     * ConfirmShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinConfirmShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ConfirmShiftUseCase::class, fn () => $self->confirmShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->confirmShiftUseCase = Mockery::mock(ConfirmShiftUseCase::class);
        });
    }
}
