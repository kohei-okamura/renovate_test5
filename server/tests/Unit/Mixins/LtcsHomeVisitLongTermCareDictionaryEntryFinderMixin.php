<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin
{
    /**
     * @var \Domain\LtcsInsCard\LtcsInsCard|\Mockery\MockInterface
     */
    protected $ltcsHomeVisitLongTermCareDictionaryEntryFinder;

    /**
     * LtcsHomeVisitLongTermCareDictionaryEntryFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsHomeVisitLongTermCareDictionaryEntryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                LtcsHomeVisitLongTermCareDictionaryEntryFinder::class,
                fn () => $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder = Mockery::mock(
                LtcsHomeVisitLongTermCareDictionaryEntryFinder::class
            );
        });
    }
}
