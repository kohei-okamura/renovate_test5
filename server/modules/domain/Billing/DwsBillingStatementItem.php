<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;

/**
 * 障害福祉サービス：明細書：明細.
 */
final class DwsBillingStatementItem extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingStatementItem} constructor
     *
     * @param \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $serviceCodeCategory サービスコード区分
     * @param int $unitScore 単位数
     * @param int $count 回数
     * @param int $totalScore サービス単位数
     */
    public function __construct(
        public readonly ServiceCode $serviceCode,
        public readonly DwsServiceCodeCategory $serviceCodeCategory,
        public readonly int $unitScore,
        public readonly int $count,
        public readonly int $totalScore
    ) {
    }
}
