<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\UpdateDwsBillingStatementUseCase;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingStatementUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateDwsBillingStatementUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\UpdateDwsBillingStatementUseCase
     */
    protected $updateDwsBillingStatementUseCase;

    /**
     * UpdateDwsBillingStatementUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateDwsBillingStatementUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UpdateDwsBillingStatementUseCase::class, fn () => $self->updateDwsBillingStatementUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->updateDwsBillingStatementUseCase = Mockery::mock(UpdateDwsBillingStatementUseCase::class);
        });
    }
}
