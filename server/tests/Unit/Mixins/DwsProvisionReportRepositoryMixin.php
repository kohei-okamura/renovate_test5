<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ProvisionReport\DwsProvisionReportRepository;
use Mockery;

/**
 * DwsProvisionReportRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsProvisionReportRepositoryMixin
{
    /**
     * @var \Domain\ProvisionReport\DwsProvisionReportRepository|\Mockery\MockInterface
     */
    protected $dwsProvisionReportRepository;

    /**
     * DwsProvisionReportRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsProvisionReportRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsProvisionReportRepository::class, fn () => $self->dwsProvisionReportRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsProvisionReportRepository = Mockery::mock(DwsProvisionReportRepository::class);
        });
    }
}
