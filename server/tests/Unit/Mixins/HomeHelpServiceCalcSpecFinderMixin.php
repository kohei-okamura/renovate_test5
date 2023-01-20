<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\HomeHelpServiceCalcSpecFinder;
use Mockery;

/**
 * {@link \Domain\Office\HomeHelpServiceCalcSpecFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait HomeHelpServiceCalcSpecFinderMixin
{
    /**
     * @var \Domain\Office\HomeHelpServiceCalcSpecFinder|\Mockery\MockInterface
     */
    protected $homeHelpServiceCalcSpecFinder;

    /**
     * {@link \Domain\Office\HomeHelpServiceCalcSpecFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinHomeHelpServiceCalcSpecFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                HomeHelpServiceCalcSpecFinder::class,
                fn () => $self->homeHelpServiceCalcSpecFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->homeHelpServiceCalcSpecFinder = Mockery::mock(
                HomeHelpServiceCalcSpecFinder::class
            );
        });
    }
}
