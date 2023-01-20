<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase
     */
    protected $findLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

    /**
     * {@link \UseCase\ServiceCodeDictionary\FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsHomeVisitLongTermCareDictionaryEntryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class,
                fn () => $self->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->findLtcsHomeVisitLongTermCareDictionaryEntryUseCase = Mockery::mock(
                FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class
            );
        });
    }
}
