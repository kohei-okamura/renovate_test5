<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\FindVisitingCareForPwsdCalcSpecUseCase;

/**
 * FindVisitingCareForPwsdCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindVisitingCareForPwsdCalcSpecUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\Office\FindVisitingCareForPwsdCalcSpecUseCase */
    protected $findVisitingCareForPwsdCalcSpecUseCase;

    /**
     * FindVisitingCareForPwsdCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindVisitingCareForPwsdCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindVisitingCareForPwsdCalcSpecUseCase::class, fn () => $self->findVisitingCareForPwsdCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findVisitingCareForPwsdCalcSpecUseCase = Mockery::mock(FindVisitingCareForPwsdCalcSpecUseCase::class);
        });
    }
}
