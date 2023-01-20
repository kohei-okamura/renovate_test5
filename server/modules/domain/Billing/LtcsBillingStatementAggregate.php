<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Billing\LtcsBillingStatementAggregateInsurance as AggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy as AggregateSubsidy;
use Domain\Common\Decimal;
use Domain\Polite;
use Lib\Exceptions\LogicException;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 介護保険サービス請求：明細書：集計情報.
 */
final class LtcsBillingStatementAggregate extends Polite
{
    private const EXPECTED_SUBSIDIES_COUNT = 3;

    /**
     * {@link \Domain\Billing\LtcsBillingStatementAggregate} constructor.
     *
     * @param \Domain\Billing\LtcsServiceDivisionCode $serviceDivisionCode サービス種類コード
     * @param int $serviceDays サービス実日数
     * @param int $plannedScore 計画単位数
     * @param int $managedScore 限度額管理対象単位数
     * @param int $unmanagedScore 限度額管理対象外単位数
     * @param \Domain\Billing\LtcsBillingStatementAggregateInsurance $insurance 保険集計結果
     * @param \Domain\Billing\LtcsBillingStatementAggregateSubsidy[] $subsidies 公費集計結果
     */
    public function __construct(
        public readonly LtcsServiceDivisionCode $serviceDivisionCode,
        public readonly int $serviceDays,
        public readonly int $plannedScore,
        public readonly int $managedScore,
        public readonly int $unmanagedScore,
        public readonly LtcsBillingStatementAggregateInsurance $insurance,
        public readonly array $subsidies
    ) {
    }

    /**
     * 介護保険サービス：明細書：集計を組み立てる.
     *
     * @param \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Seq $userSubsidies
     * @param int $benefitRate 給付率
     * @param \Domain\Billing\LtcsServiceDivisionCode $serviceDivisionCode ①サービス種類コード
     * @param int $serviceDays ③サービス実日数
     * @param int $plannedScore ④計画単位数
     * @param int $managedScore ⑤限度額管理対象単位数
     * @param int $unmanagedScore ⑥限度額管理対象外単位数
     * @param \Domain\Common\Decimal $unitCost ⑨単位数単価
     * @return self
     */
    public static function from(
        Seq $userSubsidies,
        int $benefitRate,
        LtcsServiceDivisionCode $serviceDivisionCode,
        int $serviceDays,
        int $plannedScore,
        int $managedScore,
        int $unmanagedScore,
        Decimal $unitCost
    ): self {
        if ($userSubsidies->size() !== self::EXPECTED_SUBSIDIES_COUNT) {
            $actual = $userSubsidies->size();
            throw new LogicException("Unexpected subsidies count: {$actual}");
        }

        // ⑦ 給付単位数（保険：単位数合計）
        $totalScore = self::totalScore($plannedScore, $managedScore, $unmanagedScore);

        // 介護報酬総額
        $totalAmount = self::totalAmount($totalScore, $unitCost);

        // ⑩ 保険請求額
        $claimAmount = self::insuranceClaimAmount($totalAmount, $benefitRate);

        // 公費集計結果
        $subsidies = self::generateSubsidies(
            $userSubsidies,
            $totalScore,
            $totalAmount - $claimAmount
        );

        // ⑪ 利用者負担額
        $copayAmount = self::copayAmount($totalAmount, $claimAmount, $subsidies);

        return new LtcsBillingStatementAggregate(
            serviceDivisionCode: $serviceDivisionCode,
            serviceDays: $serviceDays,
            plannedScore: $plannedScore,
            managedScore: $managedScore,
            unmanagedScore: $unmanagedScore,
            insurance: new AggregateInsurance(
                totalScore: $totalScore,
                unitCost: $unitCost,
                claimAmount: $claimAmount,
                copayAmount: $copayAmount,
            ),
            subsidies: $subsidies->toArray(),
        );
    }

    /**
     * 介護保険サービス：明細書：集計：公費集計結果を生成する.
     *
     * 優先順位順に充当していく処理を実現するため再帰的に処理を行う.
     *
     * @param \Domain\User\UserLtcsSubsidy[][]&\ScalikePHP\Option[]&\ScalikePHP\Seq $userSubsidies
     * @param int $totalScore
     * @param int $amount
     * @return \Domain\Billing\LtcsBillingStatementAggregateSubsidy[]&\ScalikePHP\Seq
     */
    private static function generateSubsidies(Seq $userSubsidies, int $totalScore, int $amount): Seq
    {
        if ($userSubsidies->isEmpty()) {
            return Seq::empty();
        }

        /**
         * @var \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Option $headOption
         * @var \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Seq $tail
         */
        $headOption = $userSubsidies->head();
        $tail = $userSubsidies->tail();
        if ($headOption->isEmpty() || $amount <= 0) {
            return Seq::from(
                AggregateSubsidy::empty(),
                ...self::generateSubsidies($tail, $totalScore, $amount),
            );
        }

        /** @var \Domain\User\UserLtcsSubsidy $head */
        $head = $headOption->get();
        $copayAmount = min($head->copay, $amount);
        $claimAmount = Math::floor(($amount - $copayAmount) * $head->benefitRate / 100);
        return Seq::from(
            new AggregateSubsidy(
                totalScore: $totalScore,
                claimAmount: $claimAmount,
                copayAmount: $copayAmount
            ),
            ...self::generateSubsidies($tail, $totalScore, $amount - $copayAmount - $claimAmount),
        );
    }

    /**
     * 給付単位数（保険：単位数合計）
     *
     * @param int $plannedScore 計画単位数
     * @param int $managedScore 限度額管理対象単位数
     * @param int $unmanagedScore 限度額管理対象外単位数
     * @return int
     */
    private static function totalScore(int $plannedScore, int $managedScore, int $unmanagedScore): int
    {
        // ⑦ 給付単位数（保険：単位数合計）
        return min($plannedScore, $managedScore) + $unmanagedScore;
    }

    /**
     * 介護報酬総額を計算する.
     *
     * @param int $totalScore 合計単位数
     * @param Decimal $unitCost 単位数単価
     * @return int
     */
    private static function totalAmount(int $totalScore, Decimal $unitCost): int
    {
        // 介護報酬総額 = 合計単位数 × 単位数単価（端数切り捨て）
        return Math::floor($totalScore * $unitCost->toInt(2) / 100);
    }

    /**
     * 保険請求額を計算する.
     *
     * @param int $totalAmount 介護報酬総額
     * @param int $benefitRate 保険給付率
     */
    private static function insuranceClaimAmount(int $totalAmount, int $benefitRate): int
    {
        // ⑩ 保険請求額 = 介護報酬総額 × 保険給付率（端数切り捨て）
        return Math::floor($totalAmount * $benefitRate / 100);
    }

    /**
     * 利用者負担額を計算する
     *
     * @param int $totalAmount 介護報酬総額
     * @param int $claimAmount 保険請求額
     * @param \Domain\Billing\LtcsBillingStatementAggregateSubsidy[]&\ScalikePHP\Seq $subsidies 公費集計結果
     */
    private static function copayAmount(int $totalAmount, int $claimAmount, Seq $subsidies): int
    {
        // 公費請求額合計
        $totalSubsidyClaimAmount = $subsidies->map(fn (AggregateSubsidy $x): int => $x->claimAmount)->sum();
        // ⑪ 利用者負担額 = 介護報酬総額 - 保険請求額 - 公費請求額合計
        return $totalAmount - $claimAmount - $totalSubsidyClaimAmount;
    }
}
