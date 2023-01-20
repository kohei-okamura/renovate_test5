<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdDictionaryEntryFinderMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdDictionaryEntryFinder;

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdDictionaryEntryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdDictionaryEntryFinder::class,
                fn () => $self->dwsVisitingCareForPwsdDictionaryEntryFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdDictionaryEntryFinder = Mockery::mock(
                DwsVisitingCareForPwsdDictionaryEntryFinder::class
            );
        });
    }
}
