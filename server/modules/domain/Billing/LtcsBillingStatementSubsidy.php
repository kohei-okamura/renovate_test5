<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\DefrayerCategory;
use Domain\Polite;

/**
 * 介護保険サービス：明細書：公費請求内容.
 */
final class LtcsBillingStatementSubsidy extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementSubsidy} constructor.
     *
     * @param null|\Domain\Common\DefrayerCategory $defrayerCategory 公費制度（法別番号）
     * @param string $defrayerNumber 負担者番号
     * @param string $recipientNumber 受給者番号
     * @param null|int $benefitRate 給付率
     * @param int $totalScore サービス単位数
     * @param int $claimAmount 請求額
     * @param int $copayAmount 利用者負担額
     */
    public function __construct(
        public readonly ?DefrayerCategory $defrayerCategory,
        public readonly string $defrayerNumber,
        public readonly string $recipientNumber,
        public readonly ?int $benefitRate,
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
            defrayerCategory: null,
            defrayerNumber: '',
            recipientNumber: '',
            benefitRate: null,
            totalScore: 0,
            claimAmount: 0,
            copayAmount: 0,
        );
    }
}
