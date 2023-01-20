<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Project\EditLtcsProjectUseCase;

/**
 * EditLtcsProjectUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditLtcsProjectUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Project\EditLtcsProjectUseCase
     */
    protected $editLtcsProjectUseCase;

    /**
     * EditLtcsProjectUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditLtcsProjectUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditLtcsProjectUseCase::class, fn () => $self->editLtcsProjectUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editLtcsProjectUseCase = Mockery::mock(EditLtcsProjectUseCase::class);
        });
    }
}
