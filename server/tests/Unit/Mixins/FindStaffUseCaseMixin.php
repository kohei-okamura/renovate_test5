<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\FindStaffUseCase;

/**
 * FindStaffUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindStaffUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\FindStaffUseCase
     */
    protected $findStaffUseCase;

    /**
     * FindStaffUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindStaffUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindStaffUseCase::class, fn () => $self->findStaffUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findStaffUseCase = Mockery::mock(FindStaffUseCase::class);
        });
    }
}
