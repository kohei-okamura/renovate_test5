<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\CreateDwsBillingBundleListUseCase;

/**
 * {@link \UseCase\Billing\CreateDwsBillingBundleListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateDwsBillingBundleListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\CreateDwsBillingBundleListUseCase
     */
    protected $createDwsBillingBundleListUseCase;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingBundleListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateDwsBillingBundleUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateDwsBillingBundleListUseCase::class,
                fn () => $self->createDwsBillingBundleListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createDwsBillingBundleListUseCase = Mockery::mock(
                CreateDwsBillingBundleListUseCase::class
            );
        });
    }
}
