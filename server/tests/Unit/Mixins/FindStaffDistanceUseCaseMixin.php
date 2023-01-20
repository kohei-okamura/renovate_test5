<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\FindStaffDistanceUseCase;

/**
 * FindStaffDistanceUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindStaffDistanceUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\FindStaffDistanceUseCase
     */
    protected $findStaffDistanceUseCase;

    /**
     * FindStaffDistanceUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindStaffDistanceUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindStaffDistanceUseCase::class, fn () => $self->findStaffDistanceUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findStaffDistanceUseCase = Mockery::mock(FindStaffDistanceUseCase::class);
        });
    }
}
