<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsHomeVisitLongTermCareDictionaryFinderMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder|\Mockery\MockInterface
     */
    protected $ltcsHomeVisitLongTermCareDictionaryFinder;

    /**
     * LtcsHomeVisitLongTermCareDictionaryFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsHomeVisitLongTermCareDictionaryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                LtcsHomeVisitLongTermCareDictionaryFinder::class,
                fn () => $self->ltcsHomeVisitLongTermCareDictionaryFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryFinder = Mockery::mock(
                LtcsHomeVisitLongTermCareDictionaryFinder::class
            );
        });
    }
}
