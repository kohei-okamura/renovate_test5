<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsVisitingCareForPwsdChunkFinder;
use Mockery;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdChunkFinderMixin
{
    /**
     * @var \Domain\Billing\DwsVisitingCareForPwsdChunkFinder|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdChunkFinder;

    /**
     * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkFinder} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdChunkFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdChunkFinder::class,
                fn () => $self->dwsVisitingCareForPwsdChunkFinder
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdChunkFinder = Mockery::mock(
                DwsVisitingCareForPwsdChunkFinder::class
            );
        });
    }
}
