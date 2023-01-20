<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\DwsAreaGrade\DwsAreaGradeRepository;
use Mockery;

/**
 * DwsAreaGradeRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsAreaGradeRepositoryMixin
{
    /**
     * @var \Domain\DwsAreaGrade\DwsAreaGradeRepository|\Mockery\MockInterface
     */
    protected $dwsAreaGradeRepository;

    /**
     * DwsAreaGradeRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsAreaGradeRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsAreaGradeRepository::class, fn () => $self->dwsAreaGradeRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsAreaGradeRepository = Mockery::mock(DwsAreaGradeRepository::class);
        });
    }
}
