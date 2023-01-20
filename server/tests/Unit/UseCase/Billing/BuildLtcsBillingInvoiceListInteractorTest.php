<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingInvoice;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsBillingInvoiceListInteractor;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingInvoiceListInteractor} のテスト.
 */
final class BuildLtcsBillingInvoiceListInteractorTest extends Test
{
    use CarbonMixin;
    use BuildLtcsBillingInvoiceListInteractorTestData;
    use DummyContextMixin;
    use LtcsBillingInvoiceRepositoryMixin;
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Seq $statements;

    private BuildLtcsBillingInvoiceListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->statements = $self->statements();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(BuildLtcsBillingInvoiceListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of LtcsBillingInvoice', function (): void {
            $actual = $this->interactor->handle($this->context, $this->bundle, $this->statements);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof LtcsBillingInvoice);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
