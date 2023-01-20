<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 介護保険公費情報検索ユースケース.
 */
interface FindUserLtcsSubsidyUseCase
{
    /**
     * 介護保険公費情報を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParam
     * @param array $paginationParam
     * @return \Domain\FinderResult
     */
    public function handle(
        Context $context,
        Permission $permission,
        array $filterParam,
        array $paginationParam
    ): FinderResult;
}
