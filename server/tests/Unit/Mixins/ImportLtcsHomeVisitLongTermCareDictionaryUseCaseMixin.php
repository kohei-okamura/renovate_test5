<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportLtcsHomeVisitLongTermCareDictionaryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryUseCase
     */
    protected $importLtcsHomeVisitLongTermCareDictionaryUseCase;

    /**
     * ImportLtcsHomeVisitLongTermCareDictionaryUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportLtcsHomeVisitLongTermCareDictionaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ImportLtcsHomeVisitLongTermCareDictionaryUseCase::class,
                fn () => $self->importLtcsHomeVisitLongTermCareDictionaryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->importLtcsHomeVisitLongTermCareDictionaryUseCase = Mockery::mock(
                ImportLtcsHomeVisitLongTermCareDictionaryUseCase::class
            );
        });
    }
}
