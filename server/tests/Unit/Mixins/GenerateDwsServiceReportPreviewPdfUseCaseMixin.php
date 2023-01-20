<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase;

/**
 * {@link \UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateDwsServiceReportPreviewPdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase
     */
    protected $generateDwsServiceReportPreviewPdfUseCase;

    /**
     * {@link \UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateDwsServiceReportPreviewPdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GenerateDwsServiceReportPreviewPdfUseCase::class,
                fn () => $self->generateDwsServiceReportPreviewPdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->generateDwsServiceReportPreviewPdfUseCase = Mockery::mock(
                GenerateDwsServiceReportPreviewPdfUseCase::class
            );
        });
    }
}
