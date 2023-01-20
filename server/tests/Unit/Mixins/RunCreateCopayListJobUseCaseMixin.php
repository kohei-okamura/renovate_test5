<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunCreateCopayListJobUseCase;

/**
 * {@link \UseCase\Billing\RunCreateCopayListJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCreateCopayListJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunCreateCopayListJobUseCase
     */
    protected $runCreateCopayListJobUseCase;

    /**
     * {@link \UseCase\Billing\RunCreateCopayListJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCreateCopayListJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCreateCopayListJobUseCase::class,
                fn () => $self->runCreateCopayListJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCreateCopayListJobUseCase = Mockery::mock(
                RunCreateCopayListJobUseCase::class
            );
        });
    }
}
