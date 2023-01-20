<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;

/**
 * 介護保険サービス：明細書：明細.
 */
final class LtcsBillingStatementItem extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementItem} constructor.
     *
     * @param \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $serviceCodeCategory サービスコード区分
     * @param int $unitScore 単位数
     * @param int $count 日数・回数
     * @param int $totalScore サービス単位数
     * @param \Domain\Billing\LtcsBillingStatementItemSubsidy[] $subsidies 公費
     * @param string $note 摘要
     */
    public function __construct(
        public readonly ServiceCode $serviceCode,
        public readonly LtcsServiceCodeCategory $serviceCodeCategory,
        public readonly int $unitScore,
        public readonly int $count,
        public readonly int $totalScore,
        public readonly array $subsidies,
        public readonly string $note
    ) {
    }
}
