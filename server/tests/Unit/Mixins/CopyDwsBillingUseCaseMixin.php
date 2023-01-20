<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CopyDwsBillingUseCase;

/**
 * {@link \UseCase\Billing\CopyDwsBillingUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CopyDwsBillingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CopyDwsBillingUseCase
     */
    protected $copyDwsBillingUseCase;

    /**
     * {@link \UseCase\Billing\CopyDwsBillingUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCopyDwsBillingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CopyDwsBillingUseCase::class,
                fn () => $self->copyDwsBillingUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->copyDwsBillingUseCase = Mockery::mock(
                CopyDwsBillingUseCase::class
            );
        });
    }
}
