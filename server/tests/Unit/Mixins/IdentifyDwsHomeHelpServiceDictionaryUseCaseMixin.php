<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\IdentifyDwsHomeHelpServiceDictionaryUseCase
     */
    protected $identifyDwsHomeHelpServiceDictionaryUseCase;

    /**
     * IdentifyDwsHomeHelpServiceDictionaryUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyDwsHomeHelpServiceDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(IdentifyDwsHomeHelpServiceDictionaryUseCase::class, fn () => $self->identifyDwsHomeHelpServiceDictionaryUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyDwsHomeHelpServiceDictionaryUseCase = Mockery::mock(IdentifyDwsHomeHelpServiceDictionaryUseCase::class);
        });
    }
}
