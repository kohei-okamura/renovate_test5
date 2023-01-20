<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoiceFinder;
use Domain\Billing\LtcsBillingInvoicePdf;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementPdf;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：介護給付費請求書・明細書PDFパラメータ組み立てユースケース実装.
 */
class BuildLtcsBillingInvoicePdfParamInteractor implements BuildLtcsBillingInvoicePdfParamUseCase
{
    private LtcsBillingInvoiceFinder $invoiceFinder;
    private LtcsBillingStatementFinder $statementFinder;
    private LtcsHomeVisitLongTermCareDictionaryEntryFinder $dictionaryEntryFinder;

    /**
     * {@link \UseCase\Billing\BuildLtcsBillingInvoicePdfParamInteractor} Constructor.
     *
     * @param \Domain\Billing\LtcsBillingInvoiceFinder $invoiceFinder
     * @param \Domain\Billing\LtcsBillingStatementFinder $statementFinder
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder $dictionaryEntryFinder
     */
    public function __construct(
        LtcsBillingInvoiceFinder $invoiceFinder,
        LtcsBillingStatementFinder $statementFinder,
        LtcsHomeVisitLongTermCareDictionaryEntryFinder $dictionaryEntryFinder
    ) {
        $this->invoiceFinder = $invoiceFinder;
        $this->statementFinder = $statementFinder;
        $this->dictionaryEntryFinder = $dictionaryEntryFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): array
    {
        return [
            'invoice' => LtcsBillingInvoicePdf::from($billing, $bundle, $this->findInvoices($billing->id, $bundle->id)),
            'statements' => $this->createStatementPdfs($billing->office, $bundle, $this->findStatements($billing->id, $bundle->id)),
        ];
    }

    /**
     * 介護保険サービス：請求書の一覧を取得する.
     *
     * @param int $billingId
     * @param int $bundleId
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
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
     * @return \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq
     */
    private function findStatements(int $billingId, int $bundleId): Seq
    {
        $filterParams = compact('billingId', 'bundleId');
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $this->statementFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 介護保険サービス：明細書 PDF ドメインを生成する.
     *
     * @param \Domain\Billing\LtcsBillingOffice $office
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingStatementPdf[]&\ScalikePHP\Seq
     */
    private function createStatementPdfs(LtcsBillingOffice $office, LtcsBillingBundle $bundle, Seq $statements): Seq
    {
        $serviceCodeMap = $this->getServiceCodeMap($statements, $bundle->providedIn);
        return $statements->map(
            function (LtcsBillingStatement $x) use ($office, $bundle, $serviceCodeMap): LtcsBillingStatementPdf {
                return LtcsBillingStatementPdf::from($office, $bundle, $x, $serviceCodeMap);
            }
        );
    }

    /**
     * サービスコード => 辞書エントリ の Map を生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq $statements
     * @param Carbon $providedIn
     * @return \ScalikePHP\Map
     */
    private function getServiceCodeMap(Seq $statements, Carbon $providedIn): Map
    {
        $serviceCodes = $statements
            ->flatMap(fn (LtcsBillingStatement $statement): iterable => Seq::fromArray($statement->items))
            ->map(fn (LtcsBillingStatementItem $x): string => $x->serviceCode->toString())
            ->toArray();

        $paginationParams = ['all' => true, 'sortBy' => 'id', 'desc' => true];

        // 辞書が更新されても同一のサービスコードならばサービス名称は変わらないという前提で
        // Entryだけをfindし、サービスコードが存在している最新のサービス辞書のデータを取得する という思想で実装している
        $entries = $this->dictionaryEntryFinder
            ->find(['serviceCodes' => $serviceCodes, 'providedIn' => $providedIn], $paginationParams)
            ->list;

        return $entries
            ->groupBy(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
            ->mapValues(fn (Seq $x): string => $x->head()->name);
    }
}
