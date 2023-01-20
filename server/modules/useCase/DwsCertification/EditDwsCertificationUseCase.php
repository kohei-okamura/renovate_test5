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
 * 障害福祉サービス受給者証編集ユースケース.
 */
interface EditDwsCertificationUseCase
{
    /**
     * 障害福祉サービス受給者証を編集する.
     *
     * @param Context $context
     * @param int $userId
     * @param int $id
     * @param array $values
     * @return \Domain\DwsCertification\DwsCertification
     */
    public function handle(Context $context, int $userId, int $id, array $values): DwsCertification;
}
