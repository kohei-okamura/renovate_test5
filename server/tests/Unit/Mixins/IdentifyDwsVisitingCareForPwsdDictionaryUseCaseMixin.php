<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\IdentifyDwsVisitingCareForPwsdDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\IdentifyDwsVisitingCareForPwsdDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\IdentifyDwsVisitingCareForPwsdDictionaryUseCase
     */
    protected $identifyDwsVisitingCareForPwsdDictionaryUseCase;

    /**
     * IdentifyDwsVisitingCareForPwsdDictionaryUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyDwsVisitingCareForPwsdDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(IdentifyDwsVisitingCareForPwsdDictionaryUseCase::class, fn () => $self->identifyDwsVisitingCareForPwsdDictionaryUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyDwsVisitingCareForPwsdDictionaryUseCase = Mockery::mock(IdentifyDwsVisitingCareForPwsdDictionaryUseCase::class);
        });
    }
}
