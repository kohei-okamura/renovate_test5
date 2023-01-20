<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository;
use Mockery;

/**
 * DwsHomeHelpServiceDictionaryRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsHomeHelpServiceDictionaryRepositoryMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository|\Mockery\MockInterface
     */
    protected $dwsHomeHelpServiceDictionaryRepository;

    /**
     * DwsHomeHelpServiceDictionaryRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsHomeHelpServiceDictionaryRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsHomeHelpServiceDictionaryRepository::class, fn () => $self->dwsHomeHelpServiceDictionaryRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsHomeHelpServiceDictionaryRepository = Mockery::mock(DwsHomeHelpServiceDictionaryRepository::class);
        });
    }
}
