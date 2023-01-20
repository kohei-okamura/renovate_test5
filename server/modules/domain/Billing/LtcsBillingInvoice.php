<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\PoliteEntity;

/**
 * 介護保険サービス：請求書.
 */
final class LtcsBillingInvoice extends PoliteEntity
{
    /**
     * {@link \Domain\Billing\LtcsBillingInvoice} constructor.
     *
     * @param null|int $id 請求書 ID
     * @param int $billingId 請求 ID
     * @param int $bundleId 請求単位 ID
     * @param bool $isSubsidy 公費フラグ
     * @param null|\Domain\Common\DefrayerCategory $defrayerCategory 公費制度（法別番号）
     * @param int $statementCount サービス費用：件数
     * @param int $totalScore サービス費用：単位数
     * @param int $totalFee サービス費用：費用合計
     * @param int $insuranceAmount サービス費用：保険請求額
     * @param int $subsidyAmount サービス費用：公費請求額
     * @param int $copayAmount サービス費用：利用者負担
     * @param \Domain\Common\Carbon $createdAt 登録日時
     * @param \Domain\Common\Carbon $updatedAt 更新日時
     */
    public function __construct(
        ?int $id,
        public readonly int $billingId,
        public readonly int $bundleId,
        public readonly bool $isSubsidy,
        public readonly ?DefrayerCategory $defrayerCategory,
        public readonly int $statementCount,
        public readonly int $totalScore,
        public readonly int $totalFee,
        public readonly int $insuranceAmount,
        public readonly int $subsidyAmount,
        public readonly int $copayAmount,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt
    ) {
        parent::__construct($id);
    }
}
