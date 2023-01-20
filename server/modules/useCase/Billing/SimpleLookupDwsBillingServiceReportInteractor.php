<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
 * サービス提供実績記録票簡易取得ユースケース実装.
 */
class SimpleLookupDwsBillingServiceReportInteractor implements SimpleLookupDwsBillingServiceReportUseCase
{
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private DwsBillingServiceReportRepository $repository;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     */
    public function __construct(
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        DwsBillingServiceReportRepository $repository
    ) {
        $this->ensureUseCase = $ensureUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        $xs->each(
            fn (DwsBillingServiceReport $x) => $this->ensureUseCase->handle($context, $permission, $x->dwsBillingId, $x->dwsBillingBundleId)
        );
        return $xs;
    }
}
