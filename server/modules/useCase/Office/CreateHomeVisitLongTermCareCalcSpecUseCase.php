<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareCalcSpec;

/**
 * 事業所算定情報（介保・訪問介護）登録ユースケース.
 */
interface CreateHomeVisitLongTermCareCalcSpecUseCase
{
    /**
     * 事業所算定情報（介保・訪問介護）情報を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec
     * @return array
     */
    public function handle(Context $context, int $officeId, HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec): array;
}
