<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase;

/**
 * {@link \UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateLtcsProvisionReportSheetJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase
     */
    protected $runCreateLtcsProvisionReportSheetJobUseCase;

    /**
     * {@link \UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateLtcsProvisionReportSheetJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateLtcsProvisionReportSheetJobUseCase::class,
                fn () => $self->runCreateLtcsProvisionReportSheetJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateLtcsProvisionReportSheetJobUseCase = Mockery::mock(
                RunCreateLtcsProvisionReportSheetJobUseCase::class
            );
        });
    }
}
