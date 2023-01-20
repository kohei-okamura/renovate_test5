<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

class SimpleLookupDwsBillingStatementInteractor implements SimpleLookupDwsBillingStatementUseCase
{
    private EnsureDwsBillingBundleUseCase $ensureUseCase;
    private DwsBillingStatementRepository $dwsBillingStatementRepository;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\EnsureDwsBillingBundleUseCase $ensureUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $dwsBillingStatementRepository
     */
    public function __construct(
        EnsureDwsBillingBundleUseCase $ensureUseCase,
        DwsBillingStatementRepository $dwsBillingStatementRepository
    ) {
        $this->ensureUseCase = $ensureUseCase;
        $this->dwsBillingStatementRepository = $dwsBillingStatementRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->dwsBillingStatementRepository->lookup(...$ids);
        $xs->each(
            fn (DwsBillingStatement $x) => $this->ensureUseCase->handle($context, $permission, $x->dwsBillingId, $x->dwsBillingBundleId)
        );
        return $xs;
    }
}
