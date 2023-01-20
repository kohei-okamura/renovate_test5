<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\EditHomeVisitLongTermCareCalcSpecUseCase;

/**
 * EditHomeVisitLongTermCareCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditHomeVisitLongTermCareCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\EditHomeVisitLongTermCareCalcSpecUseCase
     */
    protected $editHomeVisitLongTermCareCalcSpecUseCase;

    /**
     * EditHomeVisitLongTermCareCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditHomeVisitLongTermCareCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditHomeVisitLongTermCareCalcSpecUseCase::class, fn () => $self->editHomeVisitLongTermCareCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editHomeVisitLongTermCareCalcSpecUseCase = Mockery::mock(EditHomeVisitLongTermCareCalcSpecUseCase::class);
        });
    }
}
