<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ProvisionReport\LtcsProvisionReportFinder;
use Mockery;

/**
 * LtcsProvisionReportFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProvisionReportFinderMixin
{
    /**
     * @var \Domain\ProvisionReport\LtcsProvisionReportFinder|\Mockery\MockInterface
     */
    protected $ltcsProvisionReportFinder;

    /**
     * LtcsProvisionReportFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProvisionReportFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProvisionReportFinder::class, fn () => $self->ltcsProvisionReportFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProvisionReportFinder = Mockery::mock(LtcsProvisionReportFinder::class);
        });
    }
}
