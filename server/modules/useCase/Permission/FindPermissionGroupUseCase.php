<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Permission;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * PermissionGroup一覧ユースケース.
 */
interface FindPermissionGroupUseCase
{
    /**
     * PermissionGroup一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult;
}
