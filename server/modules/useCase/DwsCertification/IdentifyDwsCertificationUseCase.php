<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 障害福祉サービス受給者証特定ユースケース.
 */
interface IdentifyDwsCertificationUseCase
{
    /**
     * 指定日の障害福祉サービス受給者証 特定処理.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Option
     */
    public function handle(Context $context, int $userId, Carbon $targetDate): Option;
}
