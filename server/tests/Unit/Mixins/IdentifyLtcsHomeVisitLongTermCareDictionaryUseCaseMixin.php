<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase
     */
    protected $identifyLtcsHomeVisitLongTermCareDictionary;

    /**
     * {@link \UseCase\ServiceCodeDictionary\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyLtcsHomeVisitLongTermCareDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase::class,
                fn () => $self->identifyLtcsHomeVisitLongTermCareDictionary
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->identifyLtcsHomeVisitLongTermCareDictionary = Mockery::mock(
                IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase::class
            );
        });
    }
}
