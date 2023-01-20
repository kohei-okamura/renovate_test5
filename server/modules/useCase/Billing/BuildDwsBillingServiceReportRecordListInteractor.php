<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Context\Context;
use Domain\Exchange\DwsBillingServiceReportItemRecord;
use Domain\Exchange\DwsBillingServiceReportSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Generator;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票レコード組み立てユースケース実装.
 */
final class BuildDwsBillingServiceReportRecordListInteractor implements BuildDwsBillingServiceReportRecordListUseCase
{
    private DwsBillingServiceReportRepository $serviceReportRepository;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingServiceReportRecordListUseCase} Constructor.
     *
     * @param \Domain\Billing\DwsBillingServiceReportRepository $serviceReportRepository
     */
    public function __construct(
        DwsBillingServiceReportRepository $serviceReportRepository
    ) {
        $this->serviceReportRepository = $serviceReportRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array
    {
        $records = $this->generateDataRecords($billing, $bundles);
        return [
            DwsControlRecord::forServiceReport($billing, $records->size()),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * データレコードを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return \ScalikePHP\ScalikeTraversableInterface&\ScalikePHP\Seq
     */
    private function generateDataRecords(DwsBilling $billing, Seq $bundles): Seq
    {
        return $bundles->flatMap(
            fn (DwsBillingBundle $bundle) => $this->generateServiceReportRecord(
                $billing,
                $bundle,
            )
        );
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票の一覧を取得する.
     *
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Map
     */
    private function findServiceReports(int $bundleId): Map
    {
        return $this->serviceReportRepository->lookupByBundleId($bundleId);
    }

    /**
     * サービス実績記録票レコードの生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @return iterable
     */
    private function generateServiceReportRecord(DwsBilling $billing, DwsBillingBundle $bundle): iterable
    {
        return $this->findServiceReports($bundle->id)
            ->values()
            ->flatten()
            ->flatMap(function (DwsBillingServiceReport $x) use ($billing, $bundle): Generator {
                yield DwsBillingServiceReportSummaryRecord::from($billing, $bundle, $x);
                yield from DwsBillingServiceReportItemRecord::from($billing, $bundle, $x);
            });
    }
}
