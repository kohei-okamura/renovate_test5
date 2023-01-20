<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\DwsAreaGrade\DwsAreaGradeFinder;
use Mockery;

/**
 * DwsAreaGradeFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsAreaGradeFinderMixin
{
    /**
     * @var \Domain\DwsAreaGrade\DwsAreaGradeFinder|\Mockery\MockInterface
     */
    protected $dwsAreaGradeFinder;

    /**
     * DwsAreaGradeFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsAreaGradeFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsAreaGradeFinder::class, fn () => $self->dwsAreaGradeFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsAreaGradeFinder = Mockery::mock(DwsAreaGradeFinder::class);
        });
    }
}
