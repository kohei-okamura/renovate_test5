<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase
     */
    protected $getLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

    /**
     * {@link \UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsHomeVisitLongTermCareDictionaryEntryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class,
                fn () => $self->getLtcsHomeVisitLongTermCareDictionaryEntryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsHomeVisitLongTermCareDictionaryEntryUseCase = Mockery::mock(
                GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class
            );
        });
    }
}
