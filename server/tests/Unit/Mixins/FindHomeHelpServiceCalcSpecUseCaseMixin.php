<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\FindHomeHelpServiceCalcSpecUseCase;

/**
 * FindHomeHelpServiceCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindHomeHelpServiceCalcSpecUseCaseMixin
{
    /** @var \Mockery\MockInterface|\UseCase\Office\FindHomeHelpServiceCalcSpecUseCase */
    protected $findHomeHelpServiceCalcSpecUseCase;

    /**
     * FindHomeHelpServiceCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindHomeHelpServiceCalcSpecUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindHomeHelpServiceCalcSpecUseCase::class, fn () => $self->findHomeHelpServiceCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findHomeHelpServiceCalcSpecUseCase = Mockery::mock(FindHomeHelpServiceCalcSpecUseCase::class);
        });
    }
}
