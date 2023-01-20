<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\FinderResult;

/**
 * StaffDistance Finder Interface.
 */
interface StaffDistanceFinder extends StaffFinder
{
    /**
     * Find entities.
     *
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function find(array $filterParams, array $paginationParams): FinderResult;
}
