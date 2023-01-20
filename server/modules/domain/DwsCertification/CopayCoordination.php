<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Model;

/**
 * 障害福祉サービス受給者証：上限管理情報.
 *
 * @property-read \Domain\DwsCertification\CopayCoordinationType $copayCoordinationType 上限管理区分
 * @property-read null|int $officeId 上限管理事業所ID
 */
final class CopayCoordination extends Model
{
    protected function attrs(): array
    {
        return [
            'copayCoordinationType',
            'officeId',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'copayCoordinationType' => true,
            'officeId' => true,
        ];
    }
}
