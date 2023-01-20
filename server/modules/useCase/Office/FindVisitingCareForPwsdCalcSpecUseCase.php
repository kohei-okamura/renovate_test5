<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 障害福祉サービス：重度訪問介護：算定情報検索ユースケース.
 */
interface FindVisitingCareForPwsdCalcSpecUseCase
{
    /**
     * 障害福祉サービス：重度訪問介護：算定情報を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParams, array $paginationParams): FinderResult;
}
