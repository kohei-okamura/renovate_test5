<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Shift\EditShiftUseCase;

/**
 * EditShiftUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditShiftUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Shift\EditShiftUseCase
     */
    protected $editShiftUseCase;

    /**
     * EditShiftUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditShiftUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditShiftUseCase::class, fn () => $self->editShiftUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editShiftUseCase = Mockery::mock(EditShiftUseCase::class);
        });
    }
}
