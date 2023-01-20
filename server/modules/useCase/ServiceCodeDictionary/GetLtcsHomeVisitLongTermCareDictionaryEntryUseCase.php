<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ取得ユースケース.
 */
interface GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリ取得を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $serviceCode
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]|\ScalikePHP\Option
     */
    public function handle(
        Context $context,
        string $serviceCode,
        Carbon $providedIn
    ): Option;
}
