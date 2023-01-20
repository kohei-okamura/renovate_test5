<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\LtcsAreaGrade\FindLtcsAreaGradeUseCase;

/**
 * FindLtcsAreaGrade Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsAreaGradeUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\LtcsAreaGrade\FindLtcsAreaGradeUseCase
     */
    protected $findLtcsAreaGradeUseCase;

    /**
     * FindLtcsAreaGradeUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsAreaGradeUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindLtcsAreaGradeUseCase::class, fn () => $self->findLtcsAreaGradeUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findLtcsAreaGradeUseCase = Mockery::mock(FindLtcsAreaGradeUseCase::class);
        });
    }
}
