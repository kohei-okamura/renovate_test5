<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobUseCase;

/**
 * {@link \UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunBulkUpdateDwsBillingStatementStatusJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobUseCase
     */
    protected $runBulkUpdateDwsBillingStatementStatusJobUseCase;

    /**
     * {@link \UseCase\Billing\RunBulkUpdateDwsBillingStatementStatusJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunBulkUpdateDwsBillingStatementStatusJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunBulkUpdateDwsBillingStatementStatusJobUseCase::class,
                fn () => $self->runBulkUpdateDwsBillingStatementStatusJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runBulkUpdateDwsBillingStatementStatusJobUseCase = Mockery::mock(
                RunBulkUpdateDwsBillingStatementStatusJobUseCase::class
            );
        });
    }
}
