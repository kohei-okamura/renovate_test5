<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsAreaGrade;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 障害福祉サービス地域区分検索ユースケース.
 */
interface FindDwsAreaGradeUseCase
{
    /**
     * 障害福祉サービス地域区分を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult;
}
