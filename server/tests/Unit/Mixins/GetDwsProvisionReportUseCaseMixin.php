<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GetDwsProvisionReportUseCase;

/**
 * {@link \UseCase\ProvisionReport\GetDwsProvisionReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetDwsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\GetDwsProvisionReportUseCase
     */
    protected $getDwsProvisionReportUseCase;

    /**
     * {@link \UseCase\ProvisionReport\GetDwsProvisionReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetDwsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetDwsProvisionReportUseCase::class,
                fn () => $self->getDwsProvisionReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getDwsProvisionReportUseCase = Mockery::mock(
                GetDwsProvisionReportUseCase::class
            );
        });
    }
}
