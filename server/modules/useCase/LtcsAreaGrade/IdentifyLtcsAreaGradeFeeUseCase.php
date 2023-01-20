<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsAreaGrade;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 介護保険サービス：地域区分単価特定ユースケース.
 */
interface IdentifyLtcsAreaGradeFeeUseCase
{
    /**
     * 対象年月日において有効な介護保険サービス：地域区分単価を返す.
     *
     * @param \Domain\Context\Context $context
     * @param int $ltcsAreaGradeId
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee[]|\ScalikePHP\Option
     */
    public function handle(Context $context, int $ltcsAreaGradeId, Carbon $targetDate): Option;
}
