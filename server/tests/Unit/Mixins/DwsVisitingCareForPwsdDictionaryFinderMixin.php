<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdDictionaryFinderMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdDictionaryFinder;

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdDictionaryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdDictionaryFinder::class,
                fn () => $self->dwsVisitingCareForPwsdDictionaryFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdDictionaryFinder = Mockery::mock(
                DwsVisitingCareForPwsdDictionaryFinder::class
            );
        });
    }
}
