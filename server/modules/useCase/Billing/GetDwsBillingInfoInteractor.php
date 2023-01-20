<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleFinder;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Billing\DwsBillingServiceReportFinder;
use Domain\Billing\DwsBillingStatementFinder;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * 障害福祉サービス：請求取得ユースケース実装.
 */
final class GetDwsBillingInfoInteractor implements GetDwsBillingInfoUseCase
{
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;
    private DwsBillingBundleFinder $bundleFinder;
    private DwsBillingCopayCoordinationFinder $copayCoordinationFinder;
    private DwsBillingServiceReportFinder $serviceReportFinder;
    private DwsBillingStatementFinder $statementFinder;

    public function __construct(
        LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        DwsBillingBundleFinder $bundleFinder,
        DwsBillingCopayCoordinationFinder $copayCoordinationFinder,
        DwsBillingServiceReportFinder $serviceReportFinder,
        DwsBillingStatementFinder $statementFinder
    ) {
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
        $this->bundleFinder = $bundleFinder;
        $this->copayCoordinationFinder = $copayCoordinationFinder;
        $this->serviceReportFinder = $serviceReportFinder;
        $this->statementFinder = $statementFinder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): array
    {
        $billing = $this->lookupDwsBillingUseCase
            ->handle($context, Permission::viewBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsBilling({$id}) not found.");
            });

        $bundleSeq = $this->bundleFinder
            ->find(['dwsBillingId' => $id], ['all' => true, 'sortBy' => 'id'])
            ->list;
        $bundles = $bundleSeq->toArray();

        $dwsBillingBundleIds = $bundleSeq->map(fn (DwsBillingBundle $x): int => $x->id)->toArray();

        $copayCoordinations = $this->copayCoordinationFinder
            ->find(compact('dwsBillingBundleIds'), ['all' => true, 'sortBy' => 'id'])
            ->list
            ->toArray();

        $reports = $this->serviceReportFinder
            ->find(compact('dwsBillingBundleIds'), ['all' => true, 'sortBy' => 'id'])
            ->list
            ->toArray();

        $statements = $this->statementFinder
            ->find(compact('dwsBillingBundleIds'), ['all' => true, 'sortBy' => 'id'])
            ->list
            ->toArray();

        return compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
    }
}
