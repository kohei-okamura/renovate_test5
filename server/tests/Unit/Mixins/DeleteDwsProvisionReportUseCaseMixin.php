<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\DeleteDwsProvisionReportUseCase;

/**
 * DeleteDwsProvisionReportUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteDwsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\DeleteDwsProvisionReportUseCase
     */
    protected $deleteDwsProvisionReportUseCase;

    /**
     * DeleteDwsProvisionReportUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDeleteDwsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteDwsProvisionReportUseCase::class, fn () => $self->deleteDwsProvisionReportUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteDwsProvisionReportUseCase = Mockery::mock(DeleteDwsProvisionReportUseCase::class);
        });
    }
}
