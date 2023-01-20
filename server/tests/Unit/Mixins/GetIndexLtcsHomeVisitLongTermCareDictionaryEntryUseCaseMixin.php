<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

/**
 * {@link \UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
     */
    protected $getIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

    /**
     * {@link \UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class,
                fn () => $self->getIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase = Mockery::mock(
                GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase::class
            );
        });
    }
}
