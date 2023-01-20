<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Mockery;

/**
 * LtcsProvisionReportRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LtcsProvisionReportRepositoryMixin
{
    /**
     * @var \Domain\ProvisionReport\LtcsProvisionReportRepository|\Mockery\MockInterface
     */
    protected $ltcsProvisionReportRepository;

    /**
     * LtcsProvisionReportRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLtcsProvisionReportRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LtcsProvisionReportRepository::class, fn () => $self->ltcsProvisionReportRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ltcsProvisionReportRepository = Mockery::mock(LtcsProvisionReportRepository::class);
        });
    }
}
