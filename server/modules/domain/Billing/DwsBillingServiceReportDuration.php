<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * サービス提供実績記録票：明細：算定時間.
 *
 * @property-read \Domain\Common\CarbonRange $period 開始時間・終了時間
 * @property-read null|\Domain\Common\Decimal $serviceDurationHours 算定時間数（整数部2桁・小数部2桁）
 * @property-read null|\Domain\Common\Decimal $movingDurationHours 移動時間数（整数部2桁・小数部2桁）
 */
final class DwsBillingServiceReportDuration extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'period',
            'serviceDurationHours',
            'movingDurationHours',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'period' => true,
            'serviceDurationHours' => true,
            'movingDurationHours' => true,
        ];
    }
}
