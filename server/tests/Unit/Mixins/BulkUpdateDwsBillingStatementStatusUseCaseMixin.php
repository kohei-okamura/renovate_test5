<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\BulkUpdateDwsBillingStatementStatusUseCase;

/**
 * {@link \UseCase\Billing\BulkUpdateDwsBillingStatementStatusUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait BulkUpdateDwsBillingStatementStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\BulkUpdateDwsBillingStatementStatusUseCase
     */
    protected $bulkUpdateDwsBillingStatementStatusUseCase;

    /**
     * {@link \UseCase\Billing\BulkUpdateDwsBillingStatementStatusUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinBulkUpdateDwsBillingStatementStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                BulkUpdateDwsBillingStatementStatusUseCase::class,
                fn () => $self->bulkUpdateDwsBillingStatementStatusUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->bulkUpdateDwsBillingStatementStatusUseCase = Mockery::mock(
                BulkUpdateDwsBillingStatementStatusUseCase::class
            );
        });
    }
}
