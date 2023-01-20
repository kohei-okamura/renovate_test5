<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\VisitingCareForPwsdCalcSpecFinder;
use Mockery;

/**
 * {@link \Domain\Office\VisitingCareForPwsdCalcSpecFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait VisitingCareForPwsdCalcSpecFinderMixin
{
    /**
     * @var \Domain\Office\VisitingCareForPwsdCalcSpecFinder|\Mockery\MockInterface
     */
    protected $visitingCareForPwsdCalcSpecFinder;

    /**
     * {@link \Domain\Office\VisitingCareForPwsdCalcSpecFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinVisitingCareForPwsdCalcSpecFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                VisitingCareForPwsdCalcSpecFinder::class,
                fn () => $self->visitingCareForPwsdCalcSpecFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->visitingCareForPwsdCalcSpecFinder = Mockery::mock(
                VisitingCareForPwsdCalcSpecFinder::class
            );
        });
    }
}
