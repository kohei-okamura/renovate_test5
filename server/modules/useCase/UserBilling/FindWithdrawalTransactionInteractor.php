<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\UserBilling\WithdrawalTransactionFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * {@link \UseCase\UserBilling\FindWithdrawalTransactionUseCase} の実装.
 */
final class FindWithdrawalTransactionInteractor implements FindWithdrawalTransactionUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\UserBilling\WithdrawalTransactionFinder $finder
     */
    public function __construct(WithdrawalTransactionFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission)
            + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}
