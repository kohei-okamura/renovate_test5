<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;

/**
 * {@link \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait IdentifyHomeHelpServiceCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase
     */
    protected $identifyHomeHelpServiceCalcSpecUseCase;

    /**
     * {@link \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinIdentifyHomeHelpServiceCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                IdentifyHomeHelpServiceCalcSpecUseCase::class,
                fn () => $self->identifyHomeHelpServiceCalcSpecUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->identifyHomeHelpServiceCalcSpecUseCase = Mockery::mock(
                IdentifyHomeHelpServiceCalcSpecUseCase::class
            );
        });
    }
}
