<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdDictionaryRepositoryMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdDictionaryRepository;

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdDictionaryRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdDictionaryRepository::class,
                fn () => $self->dwsVisitingCareForPwsdDictionaryRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdDictionaryRepository = Mockery::mock(
                DwsVisitingCareForPwsdDictionaryRepository::class
            );
        });
    }
}
