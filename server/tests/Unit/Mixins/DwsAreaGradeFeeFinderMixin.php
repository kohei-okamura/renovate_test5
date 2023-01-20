<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\DwsAreaGrade\DwsAreaGradeFeeFinder;
use Mockery;

/**
 * {@link \Domain\DwsAreaGrade\DwsAreaGradeFeeFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsAreaGradeFeeFinderMixin
{
    /**
     * @var \Domain\DwsAreaGrade\DwsAreaGradeFeeFinder|\Mockery\MockInterface
     */
    protected $dwsAreaGradeFeeFinder;

    /**
     * DwsAreaGradeFeeFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsAreaGradeFeeFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(DwsAreaGradeFeeFinder::class, fn () => $self->dwsAreaGradeFeeFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsAreaGradeFeeFinder = Mockery::mock(DwsAreaGradeFeeFinder::class);
        });
    }
}
