<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 利用者負担上限管理結果票取得ユースケース実装.
 */
final class LookupDwsBillingCopayCoordinationInteractor implements LookupDwsBillingCopayCoordinationUseCase
{
    private DwsBillingCopayCoordinationRepository $repository;
    private EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordinationRepository $repository
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase
     */
    public function __construct(
        DwsBillingCopayCoordinationRepository $repository,
        EnsureDwsBillingBundleUseCase $ensureDwsBillingBundleUseCase
    ) {
        $this->repository = $repository;
        $this->ensureDwsBillingBundleUseCase = $ensureDwsBillingBundleUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Permission $permission,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int ...$ids
    ): Seq {
        $this->ensureDwsBillingBundleUseCase->handle($context, $permission, $dwsBillingId, $dwsBillingBundleId);

        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (DwsBillingCopayCoordination $x): bool => $x->dwsBillingBundleId === $dwsBillingBundleId)
            ? $xs
            : Seq::emptySeq();
    }
}
