<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護）時間帯別提供情報.
 *
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read bool $isHospitalized 入院
 * @property-read bool $isLongHospitalized 長期入院
 * @property-read bool $isCoaching 同行（熟練同行・同行支援）
 * @property-read bool $isMoving 移動加算
 * @property-read bool $isSecondary 2人目
 * @property-read \Domain\Common\Carbon $providedOn サービス提供年月日
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $timeframe 時間帯
 * @property-read int $duration 時間数
 * @property-read int $headcount 人数
 */
final class DwsVisitingCareForPwsdDuration extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'category',
            'isHospitalized',
            'isLongHospitalized',
            'isCoaching',
            'isMoving',
            'isSecondary',
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
            'isHospitalized' => true,
            'isLongHospitalized' => true,
            'isCoaching' => true,
            'isMoving' => true,
            'isSecondary' => true,
            'providedOn' => true,
            'timeframe' => true,
            'duration' => true,
            'headcount' => true,
        ];
    }
}
