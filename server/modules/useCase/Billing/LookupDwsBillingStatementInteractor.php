<?php
/**
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

/**
 * 障害福祉サービス：明細書取得ユースケース実装.
 */
final class LookupDwsBillingStatementInteractor implements LookupDwsBillingStatementUseCase
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
    public function handle(
        Context $context,
        Permission $permission,
        int $dwsBillingId,
        int $dwsBillingBundle,
        int ...$ids
    ): iterable {
        $this->ensureUseCase->handle($context, $permission, $dwsBillingId, $dwsBillingBundle);

        $xs = $this->dwsBillingStatementRepository->lookup(...$ids);
        return $xs->forAll(
            fn (DwsBillingStatement $x): bool => $x->dwsBillingBundleId === $dwsBillingBundle
        ) ? $xs : Seq::emptySeq();
    }
}
