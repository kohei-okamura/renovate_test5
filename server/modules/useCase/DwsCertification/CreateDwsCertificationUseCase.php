<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\DwsCertification;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;

/**
 * 障害福祉サービス受給者証登録ユースケース.
 */
interface CreateDwsCertificationUseCase
{
    /**
     * 障害福祉サービス受給者証を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\DwsCertification\DwsCertification $dwsCertification
     * @return \Domain\DwsCertification\DwsCertification
     */
    public function handle(Context $context, int $userId, DwsCertification $dwsCertification): DwsCertification;
}
