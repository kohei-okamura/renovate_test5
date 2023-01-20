<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\CreateHomeVisitLongTermCareCalcSpecUseCase;

/**
 * CreateHomeVisitLongTermCareCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateHomeVisitLongTermCareCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\CreateHomeVisitLongTermCareCalcSpecUseCase
     */
    protected $createHomeVisitLongTermCareCalcSpecUseCase;

    /**
     * CreateHomeVisitLongTermCareCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateHomeVisitLongTermCareCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateHomeVisitLongTermCareCalcSpecUseCase::class, fn () => $self->createHomeVisitLongTermCareCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createHomeVisitLongTermCareCalcSpecUseCase = Mockery::mock(CreateHomeVisitLongTermCareCalcSpecUseCase::class);
        });
    }
}
