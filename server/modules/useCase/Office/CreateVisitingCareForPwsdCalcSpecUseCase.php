<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\VisitingCareForPwsdCalcSpec;

/**
 * 事業所算定情報（障害・重度訪問介護）ユースケース.
 */
interface CreateVisitingCareForPwsdCalcSpecUseCase
{
    /**
     * 事業所算定情報（障害・重度訪問介護）を登録する
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    public function handle(Context $context, int $officeId, VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec): VisitingCareForPwsdCalcSpec;
}
