<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Billing\DwsHomeHelpServiceChunkRepository;
use Mockery;

/**
 * DwsHomeHelpServiceChunkRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsHomeHelpServiceChunkRepositoryMixin
{
    /**
     * @var \Domain\Billing\DwsHomeHelpServiceChunkRepository|\Mockery\MockInterface
     */
    protected $dwsHomeHelpServiceChunkRepository;

    /**
     * DwsHomeHelpServiceChunkRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsHomeHelpServiceChunkRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsHomeHelpServiceChunkRepository::class, fn () => $self->dwsHomeHelpServiceChunkRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsHomeHelpServiceChunkRepository = Mockery::mock(DwsHomeHelpServiceChunkRepository::class);
        });
    }
}
