<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\FindDwsProvisionReportUseCase;

/**
 * FindDwsProvisionReportUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindDwsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\FindDwsProvisionReportUseCase
     */
    protected $findDwsProvisionReportUseCase;

    /**
     * FindDwsProvisionReportUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindDwsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindDwsProvisionReportUseCase::class, fn () => $self->findDwsProvisionReportUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findDwsProvisionReportUseCase = Mockery::mock(FindDwsProvisionReportUseCase::class);
        });
    }
}
