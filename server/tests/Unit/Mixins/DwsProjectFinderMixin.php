<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\DwsProjectFinder;
use Mockery;

/**
 * DwsProjectFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsProjectFinderMixin
{
    /**
     * @var \Domain\Project\DwsProjectFinder|\Mockery\MockInterface
     */
    protected $dwsProjectFinder;

    /**
     * DwsProjectFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsProjectFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsProjectFinder::class, fn () => $self->dwsProjectFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsProjectFinder = Mockery::mock(DwsProjectFinder::class);
        });
    }
}
