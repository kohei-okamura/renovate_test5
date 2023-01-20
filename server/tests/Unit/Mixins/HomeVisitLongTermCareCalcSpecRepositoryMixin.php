<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Mockery;

/**
 * HomeVisitLongTermCareCalcSpecRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait HomeVisitLongTermCareCalcSpecRepositoryMixin
{
    /**
     * @var \Domain\Office\HomeVisitLongTermCareCalcSpecRepository|\Mockery\MockInterface
     */
    protected $homeVisitLongTermCareCalcSpecRepository;

    /**
     * HomeVisitLongTermCareCalcSpecRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinHomeVisitLongTermCareCalcSpecRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(HomeVisitLongTermCareCalcSpecRepository::class, fn () => $self->homeVisitLongTermCareCalcSpecRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->homeVisitLongTermCareCalcSpecRepository = Mockery::mock(HomeVisitLongTermCareCalcSpecRepository::class);
        });
    }
}
