<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\HomeVisitLongTermCareCalcSpecFinder;
use Mockery;

/**
 * HomeVisitLongTermCareCalcSpecFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait HomeVisitLongTermCareCalcSpecFinderMixin
{
    /**
     * @var \Domain\Office\HomeVisitLongTermCareCalcSpecFinder|\Mockery\MockInterface
     */
    protected $homeVisitLongTermCareCalcSpecFinder;

    /**
     * HomeVisitLongTermCareCalcSpecFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinHomeVisitLongTermCareCalcSpecFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(HomeVisitLongTermCareCalcSpecFinder::class, fn () => $self->homeVisitLongTermCareCalcSpecFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->homeVisitLongTermCareCalcSpecFinder = Mockery::mock(HomeVisitLongTermCareCalcSpecFinder::class);
        });
    }
}
