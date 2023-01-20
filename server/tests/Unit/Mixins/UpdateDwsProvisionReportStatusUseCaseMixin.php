<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\UpdateDwsProvisionReportStatusUseCase;

/**
 * UpdateDwsProvisionReportStatusUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsProvisionReportStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\UpdateDwsProvisionReportStatusUseCase
     */
    protected $updateDwsProvisionReportStatusUseCase;

    /**
     * UpdateDwsProvisionReportStatusUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsProvisionReportStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UpdateDwsProvisionReportStatusUseCase::class, fn () => $self->updateDwsProvisionReportStatusUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->updateDwsProvisionReportStatusUseCase = Mockery::mock(UpdateDwsProvisionReportStatusUseCase::class);
        });
    }
}
