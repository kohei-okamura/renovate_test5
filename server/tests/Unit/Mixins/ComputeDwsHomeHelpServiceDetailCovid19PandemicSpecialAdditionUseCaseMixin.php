<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase;

/**
 * {@link \UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase
     */
    protected $computeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase;

    /**
     * {@link \UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase::class,
                fn () => $self->computeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->computeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase = Mockery::mock(
                ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase::class
            );
        });
    }
}
