<?php

declare(strict_types=1);
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingRepository;
use ScalikePHP\Seq;

/**
 * 利用者請求取得ユースケース実装.
 */
class LookupUserBillingInteractor implements LookupUserBillingUseCase
{
    private UserBillingRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\UserBilling\UserBillingRepository $repository
     */
    public function __construct(UserBillingRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc}*/
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        $isAccessible = $xs->forAll(fn (UserBilling $x): bool => $context->isAccessibleTo(
            $permission,
            $x->organizationId,
            [$x->officeId]
        ));
        return $isAccessible ? $xs : Seq::empty();
    }
}
