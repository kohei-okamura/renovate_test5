<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin
{
    /**
     * @var \Domain\LtcsInsCard\LtcsInsCard|\Mockery\MockInterface
     */
    protected $computeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase;

    /**
     * ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(
                ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase::class,
                fn () => $self->computeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            $self->computeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase = Mockery::mock(
                ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase::class
            );
        });
    }
}
