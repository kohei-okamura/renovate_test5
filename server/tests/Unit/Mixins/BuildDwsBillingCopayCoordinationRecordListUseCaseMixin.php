<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase;

/**
 * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BuildDwsBillingCopayCoordinationRecordListUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase
     */
    protected $buildDwsBillingCopayCoordinationRecordListUseCase;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBuildDwsBillingCopayCoordinationRecordListUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BuildDwsBillingCopayCoordinationRecordListUseCase::class,
                fn () => $self->buildDwsBillingCopayCoordinationRecordListUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->buildDwsBillingCopayCoordinationRecordListUseCase = Mockery::mock(
                BuildDwsBillingCopayCoordinationRecordListUseCase::class
            );
        });
    }
}
