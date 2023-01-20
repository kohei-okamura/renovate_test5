<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 障害福祉サービス：利用者別算定情報検索ユースケース.
 */
interface FindUserDwsCalcSpecUseCase
{
    /**
     * 障害福祉サービス：利用者別算定情報を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParam
     * @param array $paginationParam
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParam, array $paginationParam): FinderResult;
}
