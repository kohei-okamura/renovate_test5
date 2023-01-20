<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Shift\ShiftRepository;
use Mockery;

/**
 * ShiftRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ShiftRepositoryMixin
{
    /**
     * @var \Domain\Shift\ShiftRepository|\Mockery\MockInterface
     */
    protected $shiftRepository;

    public static function mixinShiftRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ShiftRepository::class, fn () => $self->shiftRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->shiftRepository = Mockery::mock(ShiftRepository::class);
        });
    }
}
