<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\EditDwsProjectUseCase;

/**
 * EditDwsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditDwsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\EditDwsProjectUseCase
     */
    protected $editDwsProjectUseCase;

    /**
     * EditDwsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditDwsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditDwsProjectUseCase::class, fn () => $self->editDwsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editDwsProjectUseCase = Mockery::mock(EditDwsProjectUseCase::class);
        });
    }
}
