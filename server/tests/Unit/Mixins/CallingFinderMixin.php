<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Calling\CallingFinder;
use Mockery;

/**
 * {@link \Domain\Calling\CallingFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CallingFinderMixin
{
    /**
     * @var \Domain\Calling\CallingFinder|\Mockery\MockInterface
     */
    protected $callingFinder;

    /**
     * CallingFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCallingFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CallingFinder::class, fn () => $self->callingFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->callingFinder = Mockery::mock(CallingFinder::class);
        });
    }
}
