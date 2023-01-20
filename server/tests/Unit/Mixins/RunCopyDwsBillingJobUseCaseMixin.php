<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunCopyDwsBillingJobUseCase;

/**
 * {@link \UseCase\Billing\RunCopyDwsBillingJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunCopyDwsBillingJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunCopyDwsBillingJobUseCase
     */
    protected $runCopyDwsBillingJobUseCase;

    /**
     * {@link \UseCase\Billing\RunCopyDwsBillingJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunCopyDwsBillingJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunCopyDwsBillingJobUseCase::class,
                fn () => $self->runCopyDwsBillingJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runCopyDwsBillingJobUseCase = Mockery::mock(
                RunCopyDwsBillingJobUseCase::class
            );
        });
    }
}
