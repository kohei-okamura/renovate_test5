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
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ一覧取得ユースケース.
 */
interface GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリ一覧取得を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $filterParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $filterParams): FinderResult;
}
