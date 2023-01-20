<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス：明細書：要素.
 *
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $serviceCodeCategory サービスコード区分
 * @property-read int $unitScore 単位数
 * @property-read bool $isAddition 加算フラグ
 * @property-read int $count 回数
 * @property-read array|\Domain\Common\Carbon[] $providedOn 提供年月日
 */
final class DwsBillingStatementElement extends Model
{
    /**
     * 障害福祉サービス：明細書：明細に変換する.
     *
     * @return \Domain\Billing\DwsBillingStatementItem
     */
    public function toItem(): DwsBillingStatementItem
    {
        return new DwsBillingStatementItem(
            serviceCode: $this->serviceCode,
            serviceCodeCategory: $this->serviceCodeCategory,
            unitScore: $this->unitScore,
            count: $this->count,
            totalScore: $this->unitScore * $this->count,
        );
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'serviceCode',
            'serviceCodeCategory',
            'unitScore',
            'isAddition',
            'count',
            'providedOn',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'serviceCode' => true,
            'serviceCodeCategory' => true,
            'unitScore' => true,
            'isAddition' => true,
            'count' => true,
            'providedOn' => true,
        ];
    }
}
