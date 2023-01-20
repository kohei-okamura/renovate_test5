<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\OwnExpenseProgram\FindOwnExpenseProgramUseCase;

/**
 * FindOwnExpenseProgramUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindOwnExpenseProgramUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\OwnExpenseProgram\FindOwnExpenseProgramUseCase
     */
    protected $findOwnExpenseProgramUseCase;

    /**
     * FindOwnExpenseProgramUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindOwnExpenseProgramUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindOwnExpenseProgramUseCase::class, fn () => $self->findOwnExpenseProgramUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findOwnExpenseProgramUseCase = Mockery::mock(FindOwnExpenseProgramUseCase::class);
        });
    }
}
