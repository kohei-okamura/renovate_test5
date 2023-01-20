<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Mockery;

/**
 * DwsHomeHelpServiceDictionaryEntryFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsHomeHelpServiceDictionaryEntryFinderMixin
{
    /** @var \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder|\Mockery\MockInterface */
    protected $dwsHomeHelpServiceDictionaryEntryFinder;

    /**
     * DwsHomeHelpServiceDictionaryEntryFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsHomeHelpServiceDictionaryEntryFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsHomeHelpServiceDictionaryEntryFinder::class, fn () => $self->dwsHomeHelpServiceDictionaryEntryFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsHomeHelpServiceDictionaryEntryFinder = Mockery::mock(DwsHomeHelpServiceDictionaryEntryFinder::class);
        });
    }
}
