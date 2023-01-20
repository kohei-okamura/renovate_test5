<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Decimal;
use Domain\Polite;

/**
 * 介護保険サービス：明細書：集計：保険.
 */
final class LtcsBillingStatementAggregateInsurance extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementAggregateInsurance} constructor.
     *
     * @param int $totalScore 単位数合計
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param int $claimAmount 請求額
     * @param int $copayAmount 利用者負担額
     */
    public function __construct(
        public readonly int $totalScore,
        public readonly Decimal $unitCost,
        public readonly int $claimAmount,
        public readonly int $copayAmount
    ) {
    }
}
