<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Project\DwsProjectRepository;
use Mockery;

/**
 * DwsProjectRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsProjectRepositoryMixin
{
    /**
     * @var \Domain\Project\DwsProjectRepository|\Mockery\MockInterface
     */
    protected $dwsProjectRepository;

    /**
     * DwsProjectRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsProjectRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsProjectRepository::class, fn () => $self->dwsProjectRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsProjectRepository = Mockery::mock(DwsProjectRepository::class);
        });
    }
}
