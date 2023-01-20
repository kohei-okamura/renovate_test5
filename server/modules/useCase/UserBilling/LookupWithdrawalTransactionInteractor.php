<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionRepository;
use ScalikePHP\Seq;

class LookupWithdrawalTransactionInteractor implements LookupWithdrawalTransactionUseCase
{
    private WithdrawalTransactionRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\UserBilling\WithdrawalTransactionRepository $repository
     */
    public function __construct(WithdrawalTransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc}*/
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(fn (WithdrawalTransaction $x) => $x->organizationId === $context->organization->id)
            ? $xs
            : Seq::empty();
    }
}
