<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinder;
use Mockery;

/**
 * DwsHomeHelpServiceDictionaryFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsHomeHelpServiceDictionaryFinderMixin
{
    /** @var \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryFinder|\Mockery\MockInterface */
    protected $dwsHomeHelpServiceDictionaryFinder;

    /**
     * DwsHomeHelpServiceDictionaryFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsHomeHelpServiceDictionaryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsHomeHelpServiceDictionaryFinder::class, fn () => $self->dwsHomeHelpServiceDictionaryFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsHomeHelpServiceDictionaryFinder = Mockery::mock(DwsHomeHelpServiceDictionaryFinder::class);
        });
    }
}
