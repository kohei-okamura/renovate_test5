<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;

/**
 * サービス実績記録票取得ユースケース実装.
 */
final class GetDwsBillingServiceReportInfoInteractor implements GetDwsBillingServiceReportInfoUseCase
{
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;
    private LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase;
    private LookupDwsBillingServiceReportUseCase $lookupDwsBillingServiceReportUseCase;

    public function __construct(
        LookupDwsBillingUseCase $lookupDwsBillingUseCase,
        LookupDwsBillingBundleUseCase $lookupDwsBillingBundleUseCase,
        LookupDwsBillingServiceReportUseCase $lookupDwsBillingServiceReportUseCase
    ) {
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
        $this->lookupDwsBillingBundleUseCase = $lookupDwsBillingBundleUseCase;
        $this->lookupDwsBillingServiceReportUseCase = $lookupDwsBillingServiceReportUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $dwsBillingServiceReportId
    ): array {
        $billing = $this->lookupDwsBillingUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingId): void {
                throw new NotFoundException("DwsBilling({$dwsBillingId}) not found.");
            });

        $bundle = $this->lookupDwsBillingBundleUseCase
            ->handle($context, Permission::viewBillings(), $dwsBillingId, $dwsBillingBundleId)
            ->headOption()
            ->getOrElse(function () use ($dwsBillingBundleId): void {
                throw new NotFoundException("DwsBillingBundle({$dwsBillingBundleId}) not found.");
            });

        $report = $this->lookupDwsBillingServiceReportUseCase
            ->handle(
                $context,
                Permission::viewBillings(),
                $dwsBillingId,
                $dwsBillingBundleId,
                $dwsBillingServiceReportId
            )
            ->headOption()
            ->getOrElse(function () use ($dwsBillingServiceReportId): void {
                throw new NotFoundException("DwsBillingServiceReport({$dwsBillingServiceReportId}) not found.");
            });

        return compact('billing', 'bundle', 'report');
    }
}
