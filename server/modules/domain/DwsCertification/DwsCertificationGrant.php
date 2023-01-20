<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Model;

/**
 * 障害福祉サービス受給者証：支給量等.
 *
 * @property-read \Domain\DwsCertification\DwsCertificationServiceType $dwsCertificationServiceType サービス種別
 * @property-read string $grantedAmount 支給量等
 * @property-read \Domain\Common\Carbon $activatedOn 支給決定期間（開始）
 * @property-read \Domain\Common\Carbon $deactivatedOn 支給決定期間（終了）
 */
final class DwsCertificationGrant extends Model
{
    protected function attrs(): array
    {
        return [
            'dwsCertificationServiceType',
            'grantedAmount',
            'activatedOn',
            'deactivatedOn',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'dwsCertificationServiceType' => true,
            'grantedAmount' => true,
            'activatedOn' => true,
            'deactivatedOn' => true,
        ];
    }
}
