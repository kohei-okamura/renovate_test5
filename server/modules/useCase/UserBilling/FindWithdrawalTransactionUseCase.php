<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;

/**
 * 口座振替データ検索ユースケース.
 */
interface FindWithdrawalTransactionUseCase
{
    /**
     * 口座振替データを検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParam
     * @param array $paginationParam
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParam, array $paginationParam): FinderResult;
}
