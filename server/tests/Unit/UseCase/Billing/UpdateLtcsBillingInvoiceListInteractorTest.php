<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingInvoiceListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceFinderMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateLtcsBillingInvoiceListInteractor;

/**
 * {@link \UseCase\Billing\UpdateLtcsBillingInvoiceListInteractor} のテスト.
 */
final class UpdateLtcsBillingInvoiceListInteractorTest extends Test
{
    use BuildLtcsBillingInvoiceListInteractorTestData;
    use BuildLtcsBillingInvoiceListUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use LtcsBillingInvoiceFinderMixin;
    use LtcsBillingInvoiceRepositoryMixin;
    use LtcsBillingStatementFinderMixin;
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /** @var \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq */
    private Seq $statements;

    /** @var \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq */
    private Seq $invoices;

    /** @var \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq */
    private Seq $storedInvoices;

    private UpdateLtcsBillingInvoiceListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->statements = $self->statements();
            $self->invoices = $self->invoices(false);
            $self->storedInvoices = $self->invoices(true);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->buildLtcsBillingInvoiceListUseCase
                ->allows('handle')
                ->andReturn($self->invoices)
                ->byDefault();

            $self->ltcsBillingInvoiceFinder
                ->allows('find')
                ->andReturn(FinderResult::create([
                    'list' => $self->storedInvoices,
                    'pagination' => Pagination::create([]),
                ]))
                ->byDefault();

            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::create([
                    'list' => $self->statements,
                    'pagination' => Pagination::create([]),
                ]))
                ->byDefault();

            $id = 10;
            $self->ltcsBillingInvoiceRepository
                ->allows('store')
                ->andReturnUsing(function (LtcsBillingInvoice $x) use (&$id): LtcsBillingInvoice {
                    return empty($x->id) ? $x->copy(['id' => ++$id]) : $x;
                })
                ->byDefault();

            $self->interactor = app(UpdateLtcsBillingInvoiceListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn(Seq::empty());
            $this->buildLtcsBillingInvoiceListUseCase->expects('handle')->never();
            $this->ltcsBillingInvoiceFinder->expects('find')->never();
            $this->ltcsBillingStatementFinder->expects('find')->never();
            $this->ltcsBillingInvoiceRepository->expects('store')->never();

            $this->interactor->handle($this->context, $this->bundle);
        });
        $this->should('find invoices for update', function (): void {
            $this->ltcsBillingInvoiceFinder
                ->expects('find')
                ->with(
                    ['bundleId' => $this->bundle->id],
                    ['all' => true, 'sortBy' => 'id']
                )
                ->andReturn(FinderResult::from($this->storedInvoices, Pagination::create([])));

            $this->interactor->handle($this->context, $this->bundle);
        });
        $this->should('find statements for update invoices', function (): void {
            $this->ltcsBillingStatementFinder
                ->expects('find')
                ->with(
                    ['bundleIds' => [$this->bundle->id]],
                    ['all' => true, 'sortBy' => 'id']
                )
                ->andReturn(FinderResult::from($this->statements, Pagination::create([])));

            $this->interactor->handle($this->context, $this->bundle);
        });
        $this->should('build new invoices using BuildLtcsBillingInvoiceListUseCase', function (): void {
            $this->buildLtcsBillingInvoiceListUseCase
                ->expects('handle')
                ->with($this->context, $this->bundle, Mockery::capture($actual))
                ->andReturn($this->invoices);

            $this->interactor->handle($this->context, $this->bundle);

            $this->assertSame($this->statements, $actual);
        });
        $this->should('store invoices to LtcsBillingInvoiceRepository', function (): void {
            $this->ltcsBillingInvoiceFinder
                ->expects('find')
                ->andReturn(FinderResult::create([
                    'list' => $this->storedInvoices->take(1)->computed(),
                    'pagination' => Pagination::create([]),
                ]));
            $this->ltcsBillingInvoiceRepository
                ->expects('store')
                ->with(Mockery::capture($actual1))
                ->andReturnUsing(fn (LtcsBillingInvoice $x): LtcsBillingInvoice => $x->copy(['id' => 99]));
            $this->ltcsBillingInvoiceRepository
                ->expects('store')
                ->with(Mockery::capture($actual2))
                ->andReturnUsing(fn (LtcsBillingInvoice $x): LtcsBillingInvoice => $x->copy(['id' => 99]));

            $this->interactor->handle($this->context, $this->bundle);

            $this->assertModelStrictEquals(
                $this->invoices[0]->copy(['id' => $this->storedInvoices[0]->id]),
                $actual1
            );
            $this->assertModelStrictEquals($this->invoices[1], $actual2);
        });
        $this->should('return a Seq of LtcsBillingInvoice', function (): void {
            $expected = [
                $this->invoices[0]->copy(['id' => $this->storedInvoices[0]->id]),
                $this->invoices[1]->copy(['id' => $this->storedInvoices[1]->id]),
            ];

            $actual = $this->interactor->handle($this->context, $this->bundle);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertCount(2, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof LtcsBillingInvoice);
            $this->assertEach(
                function (LtcsBillingInvoice $e, LtcsBillingInvoice $a): void {
                    $this->assertModelStrictEquals($e, $a);
                },
                $expected,
                $actual->toArray(),
            );
        });
    }

    /**
     * テスト用の請求書一覧を生成する.
     *
     * @param bool $id
     * @return \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq
     */
    private function invoices(bool $id = false): Seq
    {
        return Seq::from(
            new LtcsBillingInvoice(
                id: $id ? 1 : null,
                billingId: $this->bundle->billingId,
                bundleId: $this->bundle->id,
                isSubsidy: false,
                defrayerCategory: null,
                statementCount: mt_rand(11, 50),
                totalScore: mt_rand(100000, 999999),
                totalFee: mt_rand(100000, 999999),
                insuranceAmount: mt_rand(100000, 999999),
                subsidyAmount: 0,
                copayAmount: mt_rand(10000, 99999),
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingInvoice(
                id: $id ? 2 : null,
                billingId: $this->bundle->billingId,
                bundleId: $this->bundle->id,
                isSubsidy: true,
                defrayerCategory: DefrayerCategory::livelihoodProtection(),
                statementCount: mt_rand(1, 10),
                totalScore: mt_rand(100000, 999999),
                totalFee: mt_rand(100000, 999999),
                insuranceAmount: 0,
                subsidyAmount: mt_rand(100000, 999999),
                copayAmount: 0,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
        );
    }
}
