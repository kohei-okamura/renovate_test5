<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\DwsAreaGrade\FindDwsAreaGradeUseCase;

/**
 * FindDwsAreaGrade Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindDwsAreaGradeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\DwsAreaGrade\FindDwsAreaGradeUseCase
     */
    protected $findDwsAreaGradeUseCase;

    /**
     * FindDwsAreaGradeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindDwsAreaGradeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindDwsAreaGradeUseCase::class, fn () => $self->findDwsAreaGradeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findDwsAreaGradeUseCase = Mockery::mock(FindDwsAreaGradeUseCase::class);
        });
    }
}
