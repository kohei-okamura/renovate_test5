<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase;

/**
 * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildLtcsProvisionReportSheetAppendixUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase
     */
    protected mixed $buildLtcsProvisionReportSheetAppendixUseCase;

    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     * @noinspection PhpUnused
     */
    public static function mixinBuildLtcsProvisionReportSheetAppendixUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildLtcsProvisionReportSheetAppendixUseCase::class,
                fn () => $self->buildLtcsProvisionReportSheetAppendixUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildLtcsProvisionReportSheetAppendixUseCase = Mockery::mock(
                BuildLtcsProvisionReportSheetAppendixUseCase::class
            );
        });
    }
}
