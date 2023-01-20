<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）時間帯別提供情報.
 *
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $providerType 提供者区分
 * @property-read bool $isSecondary 2人目
 * @property-read bool $isSpanning 日跨ぎ(2日目かどうか)
 * @property-read int $spanningDuration 日跨ぎ時間数[分] (1日目の時間数)
 * @property-read \Domain\Common\Carbon $providedOn サービス提供年月日
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $timeframe 時間帯
 * @property-read int $duration 時間数[分]
 * @property-read int $headcount 人数
 */
class DwsHomeHelpServiceDuration extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'category',
            'providerType',
            'isSecondary',
            'isSpanning',
            'spanningDuration',
            'providedOn',
            'timeframe',
            'duration',
            'headcount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'category' => true,
            'providerType' => true,
            'isSecondary' => true,
            'isSpanning' => true,
            'spanningDuration' => true,
            'providedOn' => true,
            'timeframe' => true,
            'duration' => true,
            'headcount' => true,
        ];
    }
}
