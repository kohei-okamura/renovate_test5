<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Tests\Unit\Mixins;

use Domain\Shift\ShiftFinder;
use Mockery;

/**
 * ShiftFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ShiftFinderMixin
{
    /**
     * @var \Domain\Shift\ShiftFinder|\Mockery\MockInterface
     */
    protected $shiftFinder;

    /**
     * ShiftFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinShiftFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ShiftFinder::class, fn () => $self->shiftFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->shiftFinder = Mockery::mock(ShiftFinder::class);
        });
    }
}
