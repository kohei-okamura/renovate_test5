<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase;

/**
 * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsProvisionReportSheetPdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase
     */
    protected $buildLtcsProvisionReportSheetPdfParamUseCase;

    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildLtcsProvisionReportSheetPdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsProvisionReportSheetPdfParamUseCase::class,
                fn () => $self->buildLtcsProvisionReportSheetPdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsProvisionReportSheetPdfParamUseCase = Mockery::mock(
                BuildLtcsProvisionReportSheetPdfParamUseCase::class
            );
        });
    }
}
