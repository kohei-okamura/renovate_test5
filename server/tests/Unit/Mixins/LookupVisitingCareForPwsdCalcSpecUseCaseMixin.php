<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase;

/**
 * LookupVisitingCareForPwsdCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupVisitingCareForPwsdCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupVisitingCareForPwsdCalcSpecUseCase
     */
    protected $lookupVisitingCareForPwsdCalcSpecUseCase;

    /**
     * LookupVisitingCareForPwsdCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupVisitingCareForPwsdCalcSpec(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupVisitingCareForPwsdCalcSpecUseCase::class, fn () => $self->lookupVisitingCareForPwsdCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupVisitingCareForPwsdCalcSpecUseCase = Mockery::mock(LookupVisitingCareForPwsdCalcSpecUseCase::class);
        });
    }
}
