<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportDwsVisitingCareForPwsdDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\ImportDwsVisitingCareForPwsdDictionaryUseCase
     */
    protected $importDwsVisitingCareForPwsdDictionaryUseCase;

    /**
     * ImportDwsVisitingCareForPwsdDictionaryUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportDwsVisitingCareForPwsdDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ImportDwsVisitingCareForPwsdDictionaryUseCase::class, fn () => $self->importDwsVisitingCareForPwsdDictionaryUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->importDwsVisitingCareForPwsdDictionaryUseCase = Mockery::mock(ImportDwsVisitingCareForPwsdDictionaryUseCase::class);
        });
    }
}
