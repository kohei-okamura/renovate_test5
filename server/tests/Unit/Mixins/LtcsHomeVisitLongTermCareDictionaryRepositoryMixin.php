<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository;
use Mockery;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsHomeVisitLongTermCareDictionaryRepositoryMixin
{
    /**
     * @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository|\Mockery\MockInterface
     */
    protected $ltcsHomeVisitLongTermCareDictionaryRepository;

    /**
     * LtcsHomeVisitLongTermCareDictionaryRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsHomeVisitLongTermCareDictionaryRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                LtcsHomeVisitLongTermCareDictionaryRepository::class,
                fn () => $self->ltcsHomeVisitLongTermCareDictionaryRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryRepository = Mockery::mock(
                LtcsHomeVisitLongTermCareDictionaryRepository::class
            );
        });
    }
}
