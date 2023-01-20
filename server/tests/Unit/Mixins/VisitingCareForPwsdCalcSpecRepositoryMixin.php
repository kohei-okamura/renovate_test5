<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Mockery;

/**
 * VisitingCareForPwsdCalcSpecRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait VisitingCareForPwsdCalcSpecRepositoryMixin
{
    /**
     * @var \Domain\Office\VisitingCareForPwsdCalcSpecRepository|\Mockery\MockInterface
     */
    protected $visitingCareForPwsdCalcSpecRepository;

    /**
     * VisitingCareForPwsdCalcSpecRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinVisitingCareForPwsdCalcSpecRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(VisitingCareForPwsdCalcSpecRepository::class, fn () => $self->visitingCareForPwsdCalcSpecRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->visitingCareForPwsdCalcSpecRepository = Mockery::mock(VisitingCareForPwsdCalcSpecRepository::class);
        });
    }
}
