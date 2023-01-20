<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\OwnExpenseProgram\EditOwnExpenseProgramUseCase;

/**
 * EditOwnExpenseProgramUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait EditOwnExpenseProgramUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\OwnExpenseProgram\EditOwnExpenseProgramUseCase
     */
    protected $editOwnExpenseProgramUseCase;

    /**
     * EditOwnExpenseProgramUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinEditOwnExpenseProgramUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(EditOwnExpenseProgramUseCase::class, fn () => $self->editOwnExpenseProgramUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->editOwnExpenseProgramUseCase = Mockery::mock(EditOwnExpenseProgramUseCase::class);
        });
    }
}
