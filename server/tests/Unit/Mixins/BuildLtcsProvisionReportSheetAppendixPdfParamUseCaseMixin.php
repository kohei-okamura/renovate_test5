<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase;

/**
 * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsProvisionReportSheetAppendixPdfParamUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase
     */
    protected $buildLtcsProvisionReportSheetAppendixPdfParamUseCase;

    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildLtcsProvisionReportSheetAppendixPdfParamUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsProvisionReportSheetAppendixPdfParamUseCase::class,
                fn () => $self->buildLtcsProvisionReportSheetAppendixPdfParamUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsProvisionReportSheetAppendixPdfParamUseCase = Mockery::mock(
                BuildLtcsProvisionReportSheetAppendixPdfParamUseCase::class
            );
        });
    }
}
