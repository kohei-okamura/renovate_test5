<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\CallingFinder;
use Domain\Context\Context;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 通知検索ユースケース実装.
 */
final class FindCallingInteractor implements FindCallingUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     * @param \Domain\Calling\CallingFinder $finder
     */
    public function __construct(CallingFinder $finder)
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
