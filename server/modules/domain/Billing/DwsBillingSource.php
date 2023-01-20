<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス：請求算定元データ.
 *
 * @property-read \Domain\DwsCertification\DwsCertification $certification 障害福祉サービス受給者証
 * @property-read \Domain\ProvisionReport\DwsProvisionReport $provisionReport 障害福祉サービス：予実
 * @property-read \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Option $previousProvisionReport 障害福祉サービス：前月分の予実
 */
final class DwsBillingSource extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'certification',
            'provisionReport',
            'previousProvisionReport',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'certification' => true,
            'provisionReport' => true,
            'previousProvisionReport' => true,
        ];
    }
}
