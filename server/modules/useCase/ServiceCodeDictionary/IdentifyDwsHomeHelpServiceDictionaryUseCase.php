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
 * 居宅介護サービスコード辞書特定 ユースケース.
 */
interface IdentifyDwsHomeHelpServiceDictionaryUseCase
{
    /**
     * 居宅介護サービスコード辞書を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary[]|\ScalikePHP\Option
     */
    public function handle(Context $context, Carbon $targetDate): Option;
}
