<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingUser;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingInvoiceInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingInvoiceInteractor} のテスト.
 */
final class BuildDwsBillingInvoiceInteractorTest extends Test
{
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private BuildDwsBillingInvoiceInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(BuildDwsBillingInvoiceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a DwsBillingInvoice', function (): void {
            $statements = Seq::from(
                $this->statement([
                    'user' => DwsBillingUser::from($this->users[0], $this->dwsCertifications[0]),
                    'aggregates' => $this->statementAggregates->map(fn (DwsBillingStatementAggregate $x) => $x->copy([
                        'subtotalSubsidy' => 100,
                    ]))->toArray(),
                ]),
                $this->statement([
                    'user' => DwsBillingUser::from($this->users[1], $this->dwsCertifications[1]),
                    'aggregates' => $this->statementAggregates->map(fn (DwsBillingStatementAggregate $x) => $x->copy([
                        'subtotalSubsidy' => 200,
                    ]))->toArray(),
                ]),
            );
            $actual = $this->interactor->handle($this->context, $this->bundle, $statements);

            $this->assertInstanceOf(DwsBillingInvoice::class, $actual);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
