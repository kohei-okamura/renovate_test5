<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\LookupShiftUseCase;

/**
 * LookupShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\LookupShiftUseCase
     */
    protected $lookupShiftUseCase;

    /**
     * LookupShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupShift(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupShiftUseCase::class, fn () => $self->lookupShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupShiftUseCase = Mockery::mock(LookupShiftUseCase::class);
        });
    }
}
