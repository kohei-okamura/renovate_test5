<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsVisitingCareForPwsdChunkRepository;
use Mockery;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkRepository} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsVisitingCareForPwsdChunkRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsVisitingCareForPwsdChunkRepository|\Mockery\MockInterface
     */
    protected $dwsVisitingCareForPwsdChunkRepository;

    /**
     * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkRepository} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsVisitingCareForPwsdChunkRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                DwsVisitingCareForPwsdChunkRepository::class,
                fn () => $self->dwsVisitingCareForPwsdChunkRepository
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->dwsVisitingCareForPwsdChunkRepository = Mockery::mock(
                DwsVisitingCareForPwsdChunkRepository::class
            );
        });
    }
}
