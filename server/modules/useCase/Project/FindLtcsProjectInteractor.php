<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 介護保険サービス計画検索ユースケース実装.
 */
final class FindLtcsProjectInteractor implements FindLtcsProjectUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Project\LtcsProjectFinder $finder
     */
    public function __construct(LtcsProjectFinder $finder)
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
        return 'date';
    }
}
