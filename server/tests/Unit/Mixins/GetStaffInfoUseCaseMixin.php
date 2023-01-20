<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\GetStaffInfoUseCase;

/**
 * GetStaffInfoUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetStaffInfoUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\GetStaffInfoUseCase
     */
    protected $getStaffInfoUseCase;

    /**
     * GetStaffInfoUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetStaffInfoUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(GetStaffInfoUseCase::class, fn () => $self->getStaffInfoUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->getStaffInfoUseCase = Mockery::mock(GetStaffInfoUseCase::class);
        });
    }
}
