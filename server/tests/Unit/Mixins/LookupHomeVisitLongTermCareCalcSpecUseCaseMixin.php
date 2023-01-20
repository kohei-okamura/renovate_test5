<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase;

/**
 * CreateHomeVisitLongTermCareCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupHomeVisitLongTermCareCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupHomeVisitLongTermCareCalcSpecUseCase
     */
    protected $lookupHomeVisitLongTermCareCalcSpecUseCase;

    /**
     * LookupHomeVisitLongTermCareCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupHomeVisitLongTermCareCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupHomeVisitLongTermCareCalcSpecUseCase::class, fn () => $self->lookupHomeVisitLongTermCareCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupHomeVisitLongTermCareCalcSpecUseCase = Mockery::mock(LookupHomeVisitLongTermCareCalcSpecUseCase::class);
        });
    }
}
