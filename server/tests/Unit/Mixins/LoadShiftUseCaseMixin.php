<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\LoadShiftUseCase;

/**
 * LoadShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LoadShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\LoadShiftUseCase
     */
    protected $loadShiftUseCase;

    /**
     * LoadShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLoadShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LoadShiftUseCase::class, fn () => $self->loadShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->loadShiftUseCase = Mockery::mock(LoadShiftUseCase::class);
        });
    }
}
