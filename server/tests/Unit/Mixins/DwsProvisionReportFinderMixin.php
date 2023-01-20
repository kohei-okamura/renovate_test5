<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ProvisionReport\DwsProvisionReportFinder;
use Mockery;

/**
 * DwsProvisionReportFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsProvisionReportFinderMixin
{
    /**
     * @var \Domain\ProvisionReport\DwsProvisionReportFinder|\Mockery\MockInterface
     */
    protected $dwsProvisionReportFinder;

    /**
     * DwsProvisionReportFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDwsProvisionReportFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsProvisionReportFinder::class, fn () => $self->dwsProvisionReportFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsProvisionReportFinder = Mockery::mock(DwsProvisionReportFinder::class);
        });
    }
}
