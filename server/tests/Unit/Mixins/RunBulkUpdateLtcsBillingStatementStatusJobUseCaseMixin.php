<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase;

/**
 * {@link \UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunBulkUpdateLtcsBillingStatementStatusJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase
     */
    protected $runBulkUpdateLtcsBillingStatementStatusJobUseCase;

    /**
     * {@link \UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunBulkUpdateLtcsBillingStatementStatusJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunBulkUpdateLtcsBillingStatementStatusJobUseCase::class,
                fn () => $self->runBulkUpdateLtcsBillingStatementStatusJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runBulkUpdateLtcsBillingStatementStatusJobUseCase = Mockery::mock(
                RunBulkUpdateLtcsBillingStatementStatusJobUseCase::class
            );
        });
    }
}
