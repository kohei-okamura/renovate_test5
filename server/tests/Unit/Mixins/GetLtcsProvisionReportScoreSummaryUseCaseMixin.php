<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase;

/**
 * {@link \/UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsProvisionReportScoreSummaryUseCaseMixin
{
    /**
     * @var \/UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase|\Mockery\MockInterface
     */
    protected $getLtcsProvisionReportScoreSummaryUseCase;

    /**
     * {@link \/UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsProvisionReportScoreSummaryUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsProvisionReportScoreSummaryUseCase::class,
                fn () => $self->getLtcsProvisionReportScoreSummaryUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsProvisionReportScoreSummaryUseCase = Mockery::mock(
                GetLtcsProvisionReportScoreSummaryUseCase::class
            );
        });
    }
}
