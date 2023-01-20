<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\PoliteEntity;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書.
 */
final class LtcsBillingStatement extends PoliteEntity
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatement} constructor.
     *
     * @param null|int $id 明細書 ID
     * @param int $billingId 請求 ID
     * @param int $bundleId 請求単位 ID
     * @param string $insurerNumber 保険者番号
     * @param string $insurerName 保険者名
     * @param \Domain\Billing\LtcsBillingUser $user 被保険者
     * @param \Domain\Billing\LtcsCarePlanAuthor $carePlanAuthor 居宅サービス計画
     * @param null|\Domain\Common\Carbon $agreedOn 開始年月日
     * @param null|\Domain\Common\Carbon $expiredOn 中止年月日
     * @param \Domain\Billing\LtcsExpiredReason $expiredReason 中止理由
     * @param \Domain\Billing\LtcsBillingStatementInsurance $insurance 保険請求内容
     * @param array&\Domain\Billing\LtcsBillingStatementSubsidy[] $subsidies 公費請求内容
     * @param array&\Domain\Billing\LtcsBillingStatementItem[] $items 明細
     * @param array&\Domain\Billing\LtcsBillingStatementAggregate[] $aggregates 集計
     * @param null|\Domain\ProvisionReport\LtcsProvisionReportSheetAppendix $appendix サービス提供票別表
     * @param \Domain\Billing\LtcsBillingStatus $status 状態
     * @param null|\Domain\Common\Carbon $fixedAt 確定日時
     * @param \Domain\Common\Carbon $createdAt 登録日時
     * @param \Domain\Common\Carbon $updatedAt 更新日時
     */
    public function __construct(
        ?int $id,
        public readonly int $billingId,
        public readonly int $bundleId,
        public readonly string $insurerNumber,
        public readonly string $insurerName,
        public readonly LtcsBillingUser $user,
        public readonly LtcsCarePlanAuthor $carePlanAuthor,
        public readonly ?Carbon $agreedOn,
        public readonly ?Carbon $expiredOn,
        public readonly LtcsExpiredReason $expiredReason,
        public readonly LtcsBillingStatementInsurance $insurance,
        public readonly array $subsidies,
        public readonly array $items,
        public readonly array $aggregates,
        public readonly ?LtcsProvisionReportSheetAppendix $appendix,
        public readonly LtcsBillingStatus $status,
        public readonly ?Carbon $fixedAt,
        public readonly Carbon $createdAt,
        public readonly Carbon $updatedAt
    ) {
        parent::__construct($id);
    }

    /**
     * 指定された公費区分（法別番号）を含むかどうかを判定する.
     *
     * @param \Domain\Common\DefrayerCategory $category
     * @return bool
     */
    public function includesDefrayerCategory(DefrayerCategory $category): bool
    {
        $seq = Seq::fromArray($this->subsidies);
        return $seq->exists(function (LtcsBillingStatementSubsidy $x) use ($category): bool {
            return $x->defrayerCategory === $category;
        });
    }
}
