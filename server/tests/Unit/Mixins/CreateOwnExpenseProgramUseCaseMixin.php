<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\OwnExpenseProgram\CreateOwnExpenseProgramUseCase;

/**
 * CreateOwnExpenseProgramUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateOwnExpenseProgramUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\OwnExpenseProgram\CreateOwnExpenseProgramUseCase
     */
    protected $createOwnExpenseProgramUseCase;

    /**
     * CreateOwnExpenseProgramUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateOwnExpenseProgramUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateOwnExpenseProgramUseCase::class, fn () => $self->createOwnExpenseProgramUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createOwnExpenseProgramUseCase = Mockery::mock(CreateOwnExpenseProgramUseCase::class);
        });
    }
}
