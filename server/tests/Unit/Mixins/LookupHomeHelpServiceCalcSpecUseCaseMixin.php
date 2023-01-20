<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase;

/**
 * LookupHomeHelpServiceCalcSpecUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupHomeHelpServiceCalcSpecUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Office\LookupHomeHelpServiceCalcSpecUseCase
     */
    protected $lookupHomeHelpServiceCalcSpecUseCase;

    /**
     * LookupHomeHelpServiceCalcSpecUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupHomeHelpServiceCalcSpec(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupHomeHelpServiceCalcSpecUseCase::class, fn () => $self->lookupHomeHelpServiceCalcSpecUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupHomeHelpServiceCalcSpecUseCase = Mockery::mock(LookupHomeHelpServiceCalcSpecUseCase::class);
        });
    }
}
