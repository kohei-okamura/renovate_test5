<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportDwsHomeHelpServiceDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\ImportDwsHomeHelpServiceDictionaryUseCase
     */
    protected $importDwsHomeHelpServiceDictionaryUseCase;

    /**
     * ImportDwsHomeHelpServiceDictionaryUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportDwsHomeHelpServiceDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ImportDwsHomeHelpServiceDictionaryUseCase::class, fn () => $self->importDwsHomeHelpServiceDictionaryUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->importDwsHomeHelpServiceDictionaryUseCase = Mockery::mock(ImportDwsHomeHelpServiceDictionaryUseCase::class);
        });
    }
}
