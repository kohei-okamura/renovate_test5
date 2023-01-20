<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 自治体助成情報検索ユースケース.
 */
interface FindUserDwsSubsidyUseCase
{
    /**
     * 自治体助成情報を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParam
     * @param array $paginationParam
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParam, array $paginationParam): FinderResult;
}
