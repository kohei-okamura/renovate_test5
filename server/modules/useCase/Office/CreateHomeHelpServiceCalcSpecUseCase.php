<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;

/**
 * 事業所算定情報（障害・居宅介護）登録ユースケース.
 */
interface CreateHomeHelpServiceCalcSpecUseCase
{
    /**
     * 事業所算定情報（障害・居宅介護）を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @return \Domain\Office\HomeHelpServiceCalcSpec
     */
    public function handle(Context $context, int $officeId, HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec): HomeHelpServiceCalcSpec;
}
