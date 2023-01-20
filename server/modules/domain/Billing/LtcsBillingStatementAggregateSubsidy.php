<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 介護保険サービス：明細書：集計：公費.
 */
final class LtcsBillingStatementAggregateSubsidy extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementAggregateSubsidy} constructor.
     *
     * @param int $totalScore サービス単位数
     * @param int $claimAmount 請求額
     * @param int $copayAmount 利用者負担額
     */
    public function __construct(
        public readonly int $totalScore,
        public readonly int $claimAmount,
        public readonly int $copayAmount
    ) {
    }

    /**
     * 空を表すインスタンスを生成する.
     *
     * @return static
     */
    public static function empty(): self
    {
        return new self(
            totalScore: 0,
            claimAmount: 0,
            copayAmount: 0,
        );
    }
}
