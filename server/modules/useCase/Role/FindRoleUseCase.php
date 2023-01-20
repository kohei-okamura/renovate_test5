<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 *ロール検索ユースケース.
 */
interface FindRoleUseCase
{
    /**
     * ロールを検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult;
}
