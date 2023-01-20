<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Job\EditJobUseCase;

/**
 * EditJobUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Job\EditJobUseCase
     */
    protected $editJobUseCase;

    /**
     * EditJobUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditJobUseCase::class, fn () => $self->editJobUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editJobUseCase = Mockery::mock(EditJobUseCase::class);
        });
    }
}
