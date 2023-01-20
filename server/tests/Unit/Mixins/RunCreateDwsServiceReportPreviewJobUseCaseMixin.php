<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase;

/**
 * {@link \UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateDwsServiceReportPreviewJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase
     */
    protected $runCreateDwsServiceReportPreviewJobUseCase;

    /**
     * {@link \UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateDwsServiceReportPreviewJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateDwsServiceReportPreviewJobUseCase::class,
                fn () => $self->runCreateDwsServiceReportPreviewJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateDwsServiceReportPreviewJobUseCase = Mockery::mock(
                RunCreateDwsServiceReportPreviewJobUseCase::class
            );
        });
    }
}
