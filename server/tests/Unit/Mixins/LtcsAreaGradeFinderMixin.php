<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\LtcsAreaGrade\LtcsAreaGradeFinder;
use Mockery;

/**
 * LtcsAreaGradeFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsAreaGradeFinderMixin
{
    /**
     * @var \Domain\LtcsAreaGrade\LtcsAreaGradeFinder|\Mockery\MockInterface
     */
    protected $ltcsAreaGradeFinder;

    /**
     * LtcsAreaGradeFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsAreaGradeFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsAreaGradeFinder::class, fn () => $self->ltcsAreaGradeFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsAreaGradeFinder = Mockery::mock(LtcsAreaGradeFinder::class);
        });
    }
}
