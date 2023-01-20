<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 介護保険サービス：明細書：保険請求内容.
 */
final class LtcsBillingStatementInsurance extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementInsurance} constructor.
     *
     * @param int $benefitRate 給付率
     * @param int $totalScore サービス単位数
     * @param int $claimAmount 請求額
     * @param int $copayAmount 利用者負担額
     */
    public function __construct(
        public readonly int $benefitRate,
        public readonly int $totalScore,
        public readonly int $claimAmount,
        public readonly int $copayAmount
    ) {
    }
}
