<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求単位取得ユースケース実装.
 */
final class LookupDwsBillingBundleInteractor implements LookupDwsBillingBundleUseCase
{
    private EnsureDwsBillingUseCase $ensureDwsBillingUseCase;
    private DwsBillingBundleRepository $dwsBillingBundleRepository;

    /**
     * Constructor.
     *
     * @param \UseCase\Billing\EnsureDwsBillingUseCase $ensureDwsBillingUseCase
     * @param \Domain\Billing\DwsBillingBundleRepository $dwsBillingBundleRepository
     */
    public function __construct(EnsureDwsBillingUseCase $ensureDwsBillingUseCase, DwsBillingBundleRepository $dwsBillingBundleRepository)
    {
        $this->ensureDwsBillingUseCase = $ensureDwsBillingUseCase;
        $this->dwsBillingBundleRepository = $dwsBillingBundleRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $dwsBillingId, int ...$ids): Seq
    {
        $this->ensureDwsBillingUseCase->handle($context, $permission, $dwsBillingId);

        $xs = $this->dwsBillingBundleRepository->lookup(...$ids);
        return $xs->forAll(
            fn (DwsBillingBundle $x): bool => $x->dwsBillingId === $dwsBillingId
        ) ? $xs : Seq::emptySeq();
    }
}
