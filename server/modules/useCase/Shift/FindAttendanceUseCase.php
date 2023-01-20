<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 勤務実績検索ユースケース.
 */
interface FindAttendanceUseCase
{
    /**
     * 勤務実績を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(
        Context $context,
        Permission $permission,
        array $filterParams,
        array $paginationParams
    ): FinderResult;
}
