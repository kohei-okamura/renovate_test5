<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス：請求：サービス詳細.
 *
 * @property-read int $userId 利用者ID
 * @property-read \Domain\Common\Carbon $providedOn サービス提供年月日
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $serviceCodeCategory サービスコード区分
 * @property-read bool $isAddition 加算フラグ
 * @property-read int $unitScore 単位数
 * @property-read int $count 回数
 * @property-read int $totalScore サービス単位数
 */
final class DwsBillingServiceDetail extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'userId',
            'providedOn',
            'serviceCode',
            'serviceCodeCategory',
            'isAddition',
            'unitScore',
            'count',
            'totalScore',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'userId' => true,
            'providedOn' => true,
            'serviceCode' => true,
            'serviceCodeCategory' => true,
            'isAddition' => true,
            'unitScore' => true,
            'count' => true,
            'totalScore' => true,
        ];
    }
}
