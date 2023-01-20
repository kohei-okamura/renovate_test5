<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Context\Context;
use Domain\FinderResult;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ検索ユースケース.
 */
interface FindLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリを検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult;
}
