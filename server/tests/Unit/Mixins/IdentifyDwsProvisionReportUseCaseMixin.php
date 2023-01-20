<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase;

/**
 * {@link \UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyDwsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase
     */
    protected $identifyDwsProvisionReportUseCase;

    /**
     * {@link \UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyDwsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyDwsProvisionReportUseCase::class,
                fn () => $self->identifyDwsProvisionReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyDwsProvisionReportUseCase = Mockery::mock(
                IdentifyDwsProvisionReportUseCase::class
            );
        });
    }
}
