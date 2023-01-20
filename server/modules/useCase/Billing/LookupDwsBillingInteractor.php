<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求取得ユースケース実装.
 */
final class LookupDwsBillingInteractor implements LookupDwsBillingUseCase
{
    private DwsBillingRepository $repository;

    /**
     * {@link \UseCase\Billing\LookupDwsBillingInteractor} Constructor.
     *
     * @param \Domain\Billing\DwsBillingRepository $repository
     */
    public function __construct(DwsBillingRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        $isAccessible = $xs->forAll(fn (DwsBilling $x): bool => $context->isAccessibleTo(
            $permission,
            $x->organizationId,
            [$x->office->officeId]
        ));
        return $isAccessible ? $xs : Seq::emptySeq();
    }
}
