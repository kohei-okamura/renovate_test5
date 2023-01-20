<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceFinder;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Common\DefrayerCategory;
use Domain\Context\Context;
use Domain\Exchange\EndRecord;
use Domain\Exchange\ExchangeRecord;
use Domain\Exchange\LtcsBillingInvoiceRecord;
use Domain\Exchange\LtcsBillingStatementAggregateRecord;
use Domain\Exchange\LtcsBillingStatementItemRecord;
use Domain\Exchange\LtcsBillingStatementSummaryRecord;
use Domain\Exchange\LtcsControlRecord;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：介護給付費請求書・明細書レコード組み立てユースケース.
 */
final class BuildLtcsBillingInvoiceRecordListInteractor implements BuildLtcsBillingInvoiceRecordListUseCase
{
    private LtcsBillingInvoiceFinder $invoiceFinder;
    private LtcsBillingStatementFinder $statementFinder;

    /**
     * {@link \UseCase\Billing\BuildLtcsBillingInvoiceRecordListInteractor} constructor.
     *
     * @param \Domain\Billing\LtcsBillingInvoiceFinder $invoiceFinder
     * @param \Domain\Billing\LtcsBillingStatementFinder $statementFinder
     */
    public function __construct(
        LtcsBillingInvoiceFinder $invoiceFinder,
        LtcsBillingStatementFinder $statementFinder
    ) {
        $this->invoiceFinder = $invoiceFinder;
        $this->statementFinder = $statementFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): array
    {
        $records = $this->generateDataRecords($billing, $bundle);
        return [
            LtcsControlRecord::from($billing, count($records)),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * データレコードを生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Exchange\ExchangeRecord[]
     */
    private function generateDataRecords(LtcsBilling $billing, LtcsBillingBundle $bundle): array
    {
        $invoices = $this->findInvoices($billing->id, $bundle->id);
        $statements = $this->findStatements($billing->id, $bundle->id);
        return [
            ...$this->generateInvoiceRecords($billing, $bundle, $invoices, $statements),
            ...$this->generateStatementRecords($billing, $bundle, $statements),
        ];
    }

    /**
     * データレコード：介護給付費請求書を生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \ScalikePHP\Seq $invoices
     * @param \ScalikePHP\Seq $statements
     * @return \Domain\Exchange\ExchangeRecord[]&iterable
     */
    private function generateInvoiceRecords(
        LtcsBilling $billing,
        LtcsBillingBundle $bundle,
        Seq $invoices,
        Seq $statements
    ): iterable {
        $counts = $this->computeStatementCounts($invoices, $statements);
        return $invoices->map(function (LtcsBillingInvoice $invoice) use ($billing, $bundle, $counts): ExchangeRecord {
            $statementCount = $counts
                ->find(fn (array $xs): bool => $xs['category'] === $invoice->defrayerCategory)
                ->map(fn (array $xs): int => $xs['count'])
                ->getOrElseValue(0);
            return LtcsBillingInvoiceRecord::from($billing, $bundle, $invoice, $statementCount);
        });
    }

    /**
     * データレコード：介護給付費明細書を生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \ScalikePHP\Seq $statements
     * @return \Domain\Exchange\ExchangeRecord[]&iterable
     */
    private function generateStatementRecords(
        LtcsBilling $billing,
        LtcsBillingBundle $bundle,
        Seq $statements
    ): iterable {
        return $statements->flatMap(fn (LtcsBillingStatement $statement): iterable => [
            LtcsBillingStatementSummaryRecord::from($billing, $bundle, $statement),
            ...LtcsBillingStatementItemRecord::from($billing, $bundle, $statement),
            ...LtcsBillingStatementAggregateRecord::from($billing, $bundle, $statement),
        ]);
    }

    /**
     * 法別番号ごとの明細書件数を算出する.
     *
     * @param \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq $invoices
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return array[]&\ScalikePHP\Seq
     */
    private function computeStatementCounts(Seq $invoices, Seq $statements): Seq
    {
        return $invoices
            ->map(fn (LtcsBillingInvoice $x): ?DefrayerCategory => $x->defrayerCategory)
            ->map(function (?DefrayerCategory $category) use ($statements): array {
                $count = $category === null
                    ? $statements->size()
                    : $statements
                        ->filter(function (LtcsBillingStatement $statement) use ($category): bool {
                            return $statement->includesDefrayerCategory($category);
                        })
                        ->size();
                return compact('category', 'count');
            });
    }

    /**
     * 介護保険サービス：請求書の一覧を取得する.
     *
     * @param int $billingId
     * @param int $bundleId
     * @return \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq
     */
    private function findInvoices(int $billingId, int $bundleId): Seq
    {
        $filterParams = compact('billingId', 'bundleId');
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->invoiceFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 介護保険サービス：明細書の一覧を取得する.
     *
     * @param int $billingId
     * @param int $bundleId
     * @return \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq
     */
    private function findStatements(int $billingId, int $bundleId): Seq
    {
        $filterParams = compact('billingId', 'bundleId');
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->statementFinder->find($filterParams, $paginationParams)->list;
    }
}
