<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsHomeVisitLongTermCareDictionaryEntryRepositoryMixin
{
    /**
     * @var \Domain\LtcsInsCard\LtcsInsCard|\Mockery\MockInterface
     */
    protected $ltcsHomeVisitLongTermCareDictionaryEntryRepository;

    /**
     * LtcsHomeVisitLongTermCareDictionaryEntryRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsHomeVisitLongTermCareDictionaryEntryRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                LtcsHomeVisitLongTermCareDictionaryEntryRepository::class,
                fn () => $self->ltcsHomeVisitLongTermCareDictionaryEntryRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryEntryRepository = Mockery::mock(
                LtcsHomeVisitLongTermCareDictionaryEntryRepository::class
            );
        });
    }
}
