<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsAreaGrade;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 障害福祉サービス：地域区分単価特定ユースケース.
 */
interface IdentifyDwsAreaGradeFeeUseCase
{
    /**
     * 対象年月日において有効な障害福祉サービス：地域区分単価を返す.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsAreaGradeId
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\DwsAreaGrade\DwsAreaGradeFee[]|\ScalikePHP\Option
     */
    public function handle(Context $context, int $dwsAreaGradeId, Carbon $targetDate): Option;
}
