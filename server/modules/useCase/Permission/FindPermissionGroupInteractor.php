<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Permission;

use Domain\Permission\PermissionGroupFinder;
use UseCase\FindInteractorFeature;

final class FindPermissionGroupInteractor implements FindPermissionGroupUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Permission\PermissionGroupFinder $finder
     */
    public function __construct(PermissionGroupFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'sortOrder';
    }
}
