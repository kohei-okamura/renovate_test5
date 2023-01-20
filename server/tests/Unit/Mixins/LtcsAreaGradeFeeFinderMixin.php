<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder;
use Mockery;

/**
 * {@link \Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsAreaGradeFeeFinderMixin
{
    /**
     * @var \Domain\LtcsAreaGrade\LtcsAreaGradeFeeFinder|\Mockery\MockInterface
     */
    protected $ltcsAreaGradeFeeFinder;

    /**
     * LtcsAreaGradeFeeFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsAreaGradeFeeFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(LtcsAreaGradeFeeFinder::class, fn () => $self->ltcsAreaGradeFeeFinder);
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->ltcsAreaGradeFeeFinder = Mockery::mock(LtcsAreaGradeFeeFinder::class);
        });
    }
}
