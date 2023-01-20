<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\OwnExpenseProgram\OwnExpenseProgramFinder;
use Mockery;

/**
 * OwnExpenseProgramFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OwnExpenseProgramFinderMixin
{
    /**
     * @var \Domain\OwnExpenseProgram\OwnExpenseProgramFinder|\Mockery\MockInterface
     */
    protected $ownExpenseProgramFinder;

    /**
     * OwnExpenseProgramFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOwnExpenseProgramFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OwnExpenseProgramFinder::class, fn () => $self->ownExpenseProgramFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->ownExpenseProgramFinder = Mockery::mock(OwnExpenseProgramFinder::class);
        });
    }
}
