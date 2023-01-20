<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Option;

/**
 * 介護保険サービス：訪問介護：算定情報特定ユースケース.
 */
interface IdentifyHomeVisitLongTermCareCalcSpecUseCase
{
    /**
     * 指定日において有効な介護保険サービス：訪問介護：算定情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec[]|\ScalikePHP\Option
     */
    public function handle(Context $context, Office $office, Carbon $targetDate): Option;
}
