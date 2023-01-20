<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票 取得ユースケース実装.
 */
final class LookupDwsBillingServiceReportInteractor implements LookupDwsBillingServiceReportUseCase
{
    private DwsBillingServiceReportRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase;

    /**
     * constructor.
     *
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase
     */
    public function __construct(
        DwsBillingServiceReportRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase
    ) {
        $this->repository = $repository;
        $this->ensureDwsBillingBundleUseCase = $ensureDwsBillingBundleUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $billingId, int $bundleId, int ...$ids): Seq
    {
        $this->ensureDwsBillingBundleUseCase->handle($context, $permission, $billingId, $bundleId);

        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (DwsBillingServiceReport $x): bool => $x->dwsBillingBundleId === $bundleId)
            ? $xs
            : Seq::emptySeq();
    }
}
