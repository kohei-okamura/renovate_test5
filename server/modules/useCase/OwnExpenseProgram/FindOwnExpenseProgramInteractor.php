<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\OwnExpenseProgram;

use Domain\Context\Context;
use Domain\OwnExpenseProgram\OwnExpenseProgramFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 自費サービス情報検索ユースケース実装.
 */
final class FindOwnExpenseProgramInteractor implements FindOwnExpenseProgramUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgramFinder $finder
     */
    public function __construct(OwnExpenseProgramFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission, 'officeIdsOrNull')
            + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}
