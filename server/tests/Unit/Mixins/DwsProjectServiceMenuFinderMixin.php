<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\DwsProjectServiceMenuFinder;
use Mockery;

/**
 * DwsProjectServiceMenuFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsProjectServiceMenuFinderMixin
{
    /**
     * @var \Domain\Project\DwsProjectServiceMenuFinder|\Mockery\MockInterface
     */
    protected $dwsProjectServiceMenuFinder;

    /**
     * DwsProjectServiceMenuFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsProjectServiceMenuFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsProjectServiceMenuFinder::class, fn () => $self->dwsProjectServiceMenuFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsProjectServiceMenuFinder = Mockery::mock(DwsProjectServiceMenuFinder::class);
        });
    }
}
