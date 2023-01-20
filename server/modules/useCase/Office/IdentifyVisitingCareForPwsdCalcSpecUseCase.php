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
 * 障害福祉サービス：重度訪問介護：算定情報特定ユースケース.
 */
interface IdentifyVisitingCareForPwsdCalcSpecUseCase
{
    /**
     * 指定日において有効な障害福祉サービス：重度訪問介護：算定情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec[]|\ScalikePHP\Option
     */
    public function handle(Context $context, Office $office, Carbon $targetDate): Option;
}
