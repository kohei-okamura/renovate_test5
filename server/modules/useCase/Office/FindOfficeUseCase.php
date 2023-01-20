<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 事業所検索ユースケース.
 */
interface FindOfficeUseCase
{
    /**
     * 事業所を検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $permissions, array $filterParams, array $paginationParams): FinderResult;
}
