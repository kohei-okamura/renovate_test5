<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase;

/**
 * {@link \/UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsProvisionReportTimeSummaryUseCaseMixin
{
    /**
     * @var \/UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase|\Mockery\MockInterface
     */
    protected $getDwsProvisionReportTimeSummaryUseCase;

    /**
     * {@link \/UseCase\ProvisionReport\GetDwsProvisionReportTimeSummaryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsProvisionReportTimeSummaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetDwsProvisionReportTimeSummaryUseCase::class,
                fn () => $self->getDwsProvisionReportTimeSummaryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getDwsProvisionReportTimeSummaryUseCase = Mockery::mock(
                GetDwsProvisionReportTimeSummaryUseCase::class
            );
        });
    }
}
