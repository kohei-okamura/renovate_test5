<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\OwnExpenseProgram\OwnExpenseProgramRepository;
use Mockery;

/**
 * OwnExpenseProgram Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OwnExpenseProgramRepositoryMixin
{
    /**
     * @var \Domain\OwnExpenseProgram\OwnExpenseProgramRepository|\Mockery\MockInterface
     */
    protected $ownExpenseProgramRepository;

    /**
     * OwnExpenseProgramRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOwnExpenseProgramRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OwnExpenseProgramRepository::class, fn () => $self->ownExpenseProgramRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ownExpenseProgramRepository = Mockery::mock(OwnExpenseProgramRepository::class);
        });
    }
}
