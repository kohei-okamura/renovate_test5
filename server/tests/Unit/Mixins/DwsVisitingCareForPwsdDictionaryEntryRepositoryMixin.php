<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdDictionaryEntryRepositoryMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdDictionaryEntryRepository;

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdDictionaryEntryRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdDictionaryEntryRepository::class,
                fn () => $self->dwsVisitingCareForPwsdDictionaryEntryRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdDictionaryEntryRepository = Mockery::mock(
                DwsVisitingCareForPwsdDictionaryEntryRepository::class
            );
        });
    }
}
