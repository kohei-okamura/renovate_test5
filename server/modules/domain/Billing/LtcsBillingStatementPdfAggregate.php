<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 介護保険サービス：明細書 PDF 集計.
 */
final class LtcsBillingStatementPdfAggregate extends Polite
{
    /**
     * {@link \Domain\Billing\LtcsBillingStatementPdfItem} constructor.
     *
     * @param string $serviceDivisionCode サービス種類コード
     * @param string $resolvedServiceDivisionCode サービス種類名称
     * @param string $serviceDays サービス実日数
     * @param string $plannedScore 計画単位数
     * @param string $managedScore 限度額管理対象単位数
     * @param string $unmanagedScore 限度額管理対象外単位数
     * @param string $totalScore 給付単位数
     * @param string $subsidyTotalScore 公費分単位数
     * @param string $insuranceUnitCost 単位数単価
     * @param string $insuranceClaimAmount 保険請求額
     * @param string $insuranceCopayAmount 利用者負担額
     * @param string $subsidyClaimAmount 公費請求額
     * @param string $subsidyCopayAmount 公費分本人負担
     */
    public function __construct(
        public readonly string $serviceDivisionCode,
        public readonly string $resolvedServiceDivisionCode,
        public readonly string $serviceDays,
        public readonly string $plannedScore,
        public readonly string $managedScore,
        public readonly string $unmanagedScore,
        public readonly string $totalScore,
        public readonly string $subsidyTotalScore,
        public readonly string $insuranceUnitCost,
        public readonly string $insuranceClaimAmount,
        public readonly string $insuranceCopayAmount,
        public readonly string $subsidyClaimAmount,
        public readonly string $subsidyCopayAmount,
    ) {
    }

    /**
     * 明細書集計から介護保険サービス：明細書 PDF 集計を生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementAggregate $aggregate
     * @return \Domain\Billing\LtcsBillingStatementPdfAggregate
     */
    public static function from(LtcsBillingStatementAggregate $aggregate): self
    {
        return new self(
            serviceDivisionCode: $aggregate->serviceDivisionCode->value(),
            resolvedServiceDivisionCode: LtcsServiceDivisionCode::resolve($aggregate->serviceDivisionCode),
            serviceDays: sprintf('% 2d', $aggregate->serviceDays),
            plannedScore: sprintf('% 6d', $aggregate->plannedScore),
            managedScore: sprintf('% 6d', $aggregate->managedScore),
            unmanagedScore: sprintf('% 6d', $aggregate->unmanagedScore),
            totalScore: self::totalScoreInAggregate($aggregate),
            subsidyTotalScore: isset($aggregate->subsidies[0]) ? sprintf('% 6d', $aggregate->subsidies[0]->totalScore) : str_repeat(' ', 6),
            insuranceUnitCost: sprintf('% 4d', $aggregate->insurance->unitCost->toInt(2)),
            insuranceClaimAmount: sprintf('% 6d', $aggregate->insurance->claimAmount),
            insuranceCopayAmount: sprintf('% 6d', $aggregate->insurance->copayAmount),
            subsidyClaimAmount: isset($aggregate->subsidies[0]) ? sprintf('% 6d', $aggregate->subsidies[0]->claimAmount) : str_repeat(' ', 6),
            subsidyCopayAmount: isset($aggregate->subsidies[0]) ? sprintf('% 6d', $aggregate->subsidies[0]->copayAmount) : str_repeat(' ', 6),
        );
    }

    /**
     * 給付単位数を求める.
     *
     * @param \Domain\Billing\LtcsBillingStatementAggregate $aggregate
     * @return string
     */
    private static function totalScoreInAggregate(LtcsBillingStatementAggregate $aggregate): string
    {
        $score = min($aggregate->plannedScore, $aggregate->managedScore);
        $totalScore = $score + $aggregate->unmanagedScore;
        return sprintf('% 6d', $totalScore);
    }
}
