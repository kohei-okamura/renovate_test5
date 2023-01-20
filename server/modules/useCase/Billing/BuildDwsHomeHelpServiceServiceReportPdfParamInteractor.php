<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票PDFのパラメータを組み立て実装.
 *
 * FYI: 物理名が「居宅介護向け」に見えるけど重度訪問介護についてもここで扱う.
 * TODO: 物理名を見直す.
 */
final class BuildDwsHomeHelpServiceServiceReportPdfParamInteractor implements BuildDwsHomeHelpServiceServiceReportPdfParamUseCase
{
    /**
     * {@link \UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor} Constructor.
     *
     * @param \Domain\Billing\DwsBillingServiceReportRepository $billingServiceReportRepository
     * @param \Domain\Billing\DwsBillingStatementFinder $statementFinder
     */
    public function __construct(
        private DwsBillingServiceReportRepository $billingServiceReportRepository,
        private DwsBillingStatementFinder $statementFinder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): Seq
    {
        $xs = $bundles->flatMap(function (DwsBillingBundle $bundle) use ($billing): Seq {
            return $this
                ->findServiceReports($bundle->id)
                ->flatMap(fn (DwsBillingServiceReport $report): Seq => DwsBillingServiceReportPdf::from(
                    $report,
                    $bundle->providedIn,
                    $billing->office,
                    $this->findStatement($report->dwsBillingBundleId, $report->user->userId)
                        ->headOption()
                        ->toSeq()
                        ->flatMap(fn (DwsBillingStatement $x): iterable => $x->contracts)
                ));
        });
        // このユースケース内で確実に処理を行うためにスプレッド演算子を用いる
        return Seq::from(...$xs);
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票の一覧を取得する.
     *
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    private function findServiceReports(int $bundleId): Seq
    {
        return $this->billingServiceReportRepository
            ->lookupByBundleId($bundleId)
            ->values()
            ->flatten();
    }

    /**
     * 障害福祉サービス：明細書を取得する.
     *
     * @param int $bundleId
     * @param int $userId
     * @return \ScalikePHP\Seq|
     */
    private function findStatement(int $bundleId, int $userId): Seq
    {
        return $this->statementFinder
            ->find(['dwsBillingBundleId' => $bundleId, 'userId' => $userId], ['all' => true, 'sortBy' => 'id'])
            ->list;
    }
}
