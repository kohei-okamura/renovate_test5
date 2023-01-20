<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Context\Context;
use Domain\Exchange\DwsBillingInvoiceItemRecord;
use Domain\Exchange\DwsBillingInvoiceSummaryRecord;
use Domain\Exchange\DwsBillingStatementAggregateRecord;
use Domain\Exchange\DwsBillingStatementContractRecord;
use Domain\Exchange\DwsBillingStatementDaysRecord;
use Domain\Exchange\DwsBillingStatementItemRecord;
use Domain\Exchange\DwsBillingStatementSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Generator;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：介護給付費・訓練等給付費等明細書レコード組み立てユースケース実装.
 */
final class BuildDwsBillingStatementAndInvoiceRecordListInteractor implements BuildDwsBillingStatementAndInvoiceRecordListUseCase
{
    private DwsBillingInvoiceRepository $invoiceRepository;
    private DwsBillingStatementRepository $statementRepository;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListInteractor} Constructor.
     *
     * @param \Domain\Billing\DwsBillingInvoiceRepository $invoiceRepository
     * @param \Domain\Billing\DwsBillingStatementRepository $statementRepository
     */
    public function __construct(
        DwsBillingInvoiceRepository $invoiceRepository,
        DwsBillingStatementRepository $statementRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->statementRepository = $statementRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array
    {
        $records = $this->generateDataRecords($billing, $bundles);
        return [
            DwsControlRecord::forInvoice($billing, $records->count()),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * データレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return \Domain\Exchange\ExchangeRecord[]&\ScalikePHP\Seq
     */
    private function generateDataRecords(DwsBilling $billing, Seq $bundles): Seq
    {
        return $bundles->flatMap(function (DwsBillingBundle $bundle) use ($billing): iterable {
            $invoices = $this->getInvoices($bundle->id);
            $statements = $this->getStatements($bundle->id);
            yield from $this->generateInvoiceRecord($billing, $bundle, $invoices);
            yield from $this->generateStatementRecord($billing, $bundle, $statements);
        });
    }

    /**
     * 請求書レコード生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \ScalikePHP\Seq $invoices
     * @return \Generator
     */
    private function generateInvoiceRecord(DwsBilling $billing, DwsBillingBundle $bundle, Seq $invoices): Generator
    {
        foreach ($invoices as $invoice) {
            assert($invoice instanceof DwsBillingInvoice);
            yield DwsBillingInvoiceSummaryRecord::from($billing, $bundle, $invoice);
            foreach ($invoice->items as $item) {
                yield DwsBillingInvoiceItemRecord::from($billing, $bundle, $item);
            }
        }
    }

    /**
     * 明細書レコード生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @return \Generator
     */
    private function generateStatementRecord(DwsBilling $billing, DwsBillingBundle $bundle, Seq $statements): Generator
    {
        foreach ($statements as $statement) {
            assert($statement instanceof DwsBillingStatement);
            yield DwsBillingStatementSummaryRecord::from($billing, $bundle, $statement);
            foreach ($statement->aggregates as $aggregate) {
                yield DwsBillingStatementDaysRecord::from($billing, $bundle, $statement->user, $aggregate);
            }
            foreach ($statement->items as $item) {
                yield DwsBillingStatementItemRecord::from($billing, $bundle, $statement->user, $item);
            }
            foreach ($statement->aggregates as $aggregate) {
                yield DwsBillingStatementAggregateRecord::from($billing, $bundle, $statement->user, $aggregate);
            }
            foreach ($statement->contracts as $contract) {
                yield DwsBillingStatementContractRecord::from($billing, $bundle, $statement->user, $contract);
            }
        }
    }

    /**
     * 請求書の一覧を取得する.
     *
     * @param int $dwsBillingBundleId
     * @return \Domain\Billing\DwsBillingInvoice[]&\ScalikePHP\Seq
     */
    private function getInvoices(int $dwsBillingBundleId): Seq
    {
        return $this->invoiceRepository
            ->lookupByBundleId($dwsBillingBundleId)
            ->values()
            ->flatten();
    }

    /**
     * 明細書の一覧を取得する.
     *
     * @param int $dwsBillingBundleId
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq
     */
    private function getStatements(int $dwsBillingBundleId): Seq
    {
        return $this->statementRepository
            ->lookupByBundleId($dwsBillingBundleId)
            ->headOption()
            ->getOrElseValue([0, Seq::empty()])[1];
    }
}
