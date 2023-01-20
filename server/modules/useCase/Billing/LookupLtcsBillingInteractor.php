<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingRepository;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求取得ユースケース.
 */
class LookupLtcsBillingInteractor implements LookupLtcsBillingUseCase
{
    private LtcsBillingRepository $repository;

    /**
     * constructor.
     *
     * @param \Domain\Billing\LtcsBillingRepository $repository
     */
    public function __construct(LtcsBillingRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        $isAccessible = $xs->forAll(fn (LtcsBilling $x): bool => $context->isAccessibleTo(
            $permission,
            $x->organizationId,
            [$x->office->officeId]
        ));
        return $isAccessible ? $xs : Seq::emptySeq();
    }
}
