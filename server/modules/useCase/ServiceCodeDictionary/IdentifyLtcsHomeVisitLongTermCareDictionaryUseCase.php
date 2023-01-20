<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書特定ユースケース.
 */
interface IdentifyLtcsHomeVisitLongTermCareDictionaryUseCase
{
    /**
     * 対象年月日において有効な介護保険サービス：訪問介護：サービスコード辞書を返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary[]|\ScalikePHP\Option
     */
    public function handle(Context $context, Carbon $targetDate): Option;
}
