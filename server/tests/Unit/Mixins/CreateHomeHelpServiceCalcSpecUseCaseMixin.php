<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\CreateHomeHelpServiceCalcSpecUseCase;

/**
 * CreateHomeHelpServiceCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateHomeHelpServiceCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\CreateHomeHelpServiceCalcSpecUseCase
     */
    protected $createHomeHelpServiceCalcSpecUseCase;

    /**
     * CreateHomeHelpServiceCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateHomeHelpServiceCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateHomeHelpServiceCalcSpecUseCase::class, fn () => $self->createHomeHelpServiceCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createHomeHelpServiceCalcSpecUseCase = Mockery::mock(CreateHomeHelpServiceCalcSpecUseCase::class);
        });
    }
}
