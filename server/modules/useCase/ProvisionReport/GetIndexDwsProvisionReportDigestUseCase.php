<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 障害福祉サービス：予実：概要一覧取得ユースケース.
 */
interface GetIndexDwsProvisionReportDigestUseCase
{
    /**
     * 障害福祉サービス：予実：概要一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult;
}
