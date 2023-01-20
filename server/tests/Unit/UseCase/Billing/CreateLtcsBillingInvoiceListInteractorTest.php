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
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingInvoiceListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingInvoiceListInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInvoiceListInteractor} のテスト.
 */
final class CreateLtcsBillingInvoiceListInteractorTest extends Test
{
    use BuildLtcsBillingInvoiceListUseCaseMixin;
    use BuildLtcsBillingInvoiceListInteractorTestData;
    use CarbonMixin;
    use DummyContextMixin;
    use LtcsBillingInvoiceRepositoryMixin;
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Seq $statements;
    private Seq $invoices;

    private CreateLtcsBillingInvoiceListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->statements = $self->statements();
            $self->invoices = $self->invoices();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->buildLtcsBillingInvoiceListUseCase
                ->allows('handle')
                ->andReturn($self->invoices)
                ->byDefault();

            $id = 0;
            $self->ltcsBillingInvoiceRepository
                ->allows('store')
                ->andReturnUsing(function (LtcsBillingInvoice $x) use (&$id): LtcsBillingInvoice {
                    return $x->copy(['id' => ++$id]);
                })
                ->byDefault();

            $self->interactor = app(CreateLtcsBillingInvoiceListInteractor::class);
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
            $this->ltcsBillingInvoiceRepository->expects('store')->never();

            $this->interactor->handle($this->context, $this->bundle, $this->statements);
        });
        $this->should('build invoices using BuildLtcsBillingInvoiceListUseCase', function (): void {
            $this->buildLtcsBillingInvoiceListUseCase
                ->expects('handle')
                ->with($this->context, $this->bundle, $this->statements)
                ->andReturn($this->invoices);

            $this->interactor->handle($this->context, $this->bundle, $this->statements);
        });
        $this->should('store invoices to LtcsBillingInvoiceRepository', function (): void {
            $this->ltcsBillingInvoiceRepository
                ->expects('store')
                ->with($this->invoices[0])
                ->andReturnUsing(fn (LtcsBillingInvoice $x): LtcsBillingInvoice => $x->copy(['id' => 1]));
            $this->ltcsBillingInvoiceRepository
                ->expects('store')
                ->with($this->invoices[1])
                ->andReturnUsing(fn (LtcsBillingInvoice $x): LtcsBillingInvoice => $x->copy(['id' => 2]));

            $this->interactor->handle($this->context, $this->bundle, $this->statements);
        });
        $this->should('return a Seq of LtcsBillingInvoice', function (): void {
            $actual = $this->interactor->handle($this->context, $this->bundle, $this->statements);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof LtcsBillingInvoice);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用の請求書一覧を生成する.
     *
     * @return \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq
     */
    private function invoices(): Seq
    {
        return Seq::from(
            new LtcsBillingInvoice(
                id: null,
                billingId: 2,
                bundleId: 1,
                isSubsidy: false,
                defrayerCategory: null,
                statementCount: 20,
                totalScore: 103504,
                totalFee: 1179937,
                insuranceAmount: 1047625,
                subsidyAmount: 24105,
                copayAmount: 108207,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingInvoice(
                id: null,
                billingId: 2,
                bundleId: 1,
                isSubsidy: true,
                defrayerCategory: DefrayerCategory::livelihoodProtection(),
                statementCount: 2,
                totalScore: 21144,
                totalFee: 241040,
                insuranceAmount: 0,
                subsidyAmount: 24105,
                copayAmount: 0,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
        );
    }
}
