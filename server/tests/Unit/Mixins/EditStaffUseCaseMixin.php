<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\EditStaffUseCase;

/**
 * EditStaffUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditStaffUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\EditStaffUseCase
     */
    protected $editStaffUseCase;

    /**
     * EditStaffUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditStaffUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditStaffUseCase::class, fn () => $self->editStaffUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editStaffUseCase = Mockery::mock(EditStaffUseCase::class);
        });
    }
}
