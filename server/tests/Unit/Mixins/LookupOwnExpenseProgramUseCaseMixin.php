<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase;

/**
 * LookupOwnExpenseProgramUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupOwnExpenseProgramUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\OwnExpenseProgram\LookupOwnExpenseProgramUseCase
     */
    protected $lookupOwnExpenseProgramUseCase;

    /**
     * LookupOwnExpenseProgramUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupOwnExpenseProgram(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupOwnExpenseProgramUseCase::class, fn () => $self->lookupOwnExpenseProgramUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupOwnExpenseProgramUseCase = Mockery::mock(LookupOwnExpenseProgramUseCase::class);
        });
    }
}
