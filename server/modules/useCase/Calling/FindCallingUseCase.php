<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 通知検索ユースケース.
 */
interface FindCallingUseCase
{
    /**
     * 通知を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array<string, string> $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParams, array $paginationParams): FinderResult;
}
