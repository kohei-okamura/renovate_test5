<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase;

/**
 * {@link \UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GenerateLtcsProvisionReportSheetPdfUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase
     */
    protected $generateLtcsProvisionReportSheetPdfUseCase;

    /**
     * {@link \UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGenerateLtcsProvisionReportSheetPdfUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GenerateLtcsProvisionReportSheetPdfUseCase::class,
                fn () => $self->generateLtcsProvisionReportSheetPdfUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->generateLtcsProvisionReportSheetPdfUseCase = Mockery::mock(
                GenerateLtcsProvisionReportSheetPdfUseCase::class
            );
        });
    }
}
