<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\ImportShiftUseCase;

/**
 * ImportShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait ImportShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\ImportShiftUseCase
     */
    protected $importShiftUseCase;

    /**
     * ImportShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinImportShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(ImportShiftUseCase::class, fn () => $self->importShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->importShiftUseCase = Mockery::mock(ImportShiftUseCase::class);
        });
    }
}
