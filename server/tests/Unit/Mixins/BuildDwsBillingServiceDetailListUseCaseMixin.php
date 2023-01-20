<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingServiceDetailListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceDetailListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingServiceDetailListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingServiceDetailListUseCase
     */
    protected $buildDwsBillingServiceDetailListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceDetailListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingServiceDetailListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingServiceDetailListUseCase::class,
                fn () => $self->buildDwsBillingServiceDetailListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingServiceDetailListUseCase = Mockery::mock(
                BuildDwsBillingServiceDetailListUseCase::class
            );
        });
    }
}
