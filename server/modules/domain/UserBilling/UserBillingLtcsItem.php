<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Common\ConsumptionTaxRate;
use Domain\Model;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 利用者請求：介護保険サービス明細.
 *
 * @property-read int $ltcsStatementId 介護保険明細書ID
 * @property-read int $score 単位数
 * @property-read \Domain\Common\Decimal $unitCost 単価
 * @property-read int $subtotalCost 小計
 * @property-read \Domain\Common\ConsumptionTaxRate $tax 消費税
 * @property-read int $medicalDeductionAmount 医療費控除対象額
 * @property-read int $benefitAmount 介護給付額
 * @property-read int $subsidyAmount 公費負担額
 * @property-read int $totalAmount 合計
 * @property-read int $copayWithoutTax 自己負担額（税抜）
 * @property-read int $copayWithTax 自己負担額（税込）
 */
final class UserBillingLtcsItem extends Model
{
    /**
     * 利用者請求：介護保険サービス明細 ドメインモデルを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @return static
     */
    public static function from(LtcsBillingStatement $statement): self
    {
        $score = self::computeScore($statement);
        $unitCost = $statement->aggregates[0]->insurance->unitCost;

        $benefitAmount = $statement->insurance->claimAmount;
        $subsidyAmount = Seq::fromArray($statement->subsidies)
            ->map(fn (LtcsBillingStatementSubsidy $x): int => $x->claimAmount)
            ->sum();
        $copayAmount = self::computeCopayAmount($statement);
        $subtotalCost = $benefitAmount + $subsidyAmount + $copayAmount;

        return self::create([
            'ltcsStatementId' => $statement->id,
            'score' => $score,
            'unitCost' => $unitCost,
            'subtotalCost' => $subtotalCost,
            'tax' => ConsumptionTaxRate::zero(),
            'medicalDeductionAmount' => self::computeMedicalDeductionAmount($statement, $copayAmount),
            'benefitAmount' => $benefitAmount,
            'subsidyAmount' => $subsidyAmount,
            'totalAmount' => $copayAmount,
            // 非課税のため税込・税抜は金額と同じ。
            'copayWithoutTax' => $copayAmount,
            'copayWithTax' => $copayAmount,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'ltcsStatementId',
            'score',
            'unitCost',
            'subtotalCost',
            'tax',
            'medicalDeductionAmount',
            'benefitAmount',
            'subsidyAmount',
            'totalAmount',
            'copayWithoutTax',
            'copayWithTax',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'ltcsStatementId' => true,
            'score' => true,
            'unitCost' => true,
            'subtotalCost' => true,
            'tax' => true,
            'medicalDeductionAmount' => true,
            'benefitAmount' => true,
            'subsidyAmount' => true,
            'totalAmount' => true,
            'copayWithoutTax' => true,
            'copayWithTax' => true,
        ];
    }

    /**
     * 単位数を取得する.
     *
     * 単位数については全額自己負担分を含むサービス提供票別表から取得する.
     * ただしサービス提供票別表は古い明細書には設定されていない属性のためその場合は従来通り明細書の単位数を用いる.
     *
     * サービス提供票別表が含まれない古い明細書については支給限度基準を超える単位数に対応する以前のものであり
     * 明細書の単位数が常に正しい値となる.
     *
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @return int
     */
    private static function computeScore(LtcsBillingStatement $statement): int
    {
        return $statement->appendix?->getTotalWholeScore() ?? $statement->insurance->totalScore;
    }

    /**
     * 合計（利用者負担額）を取得する.
     *
     * 合計（利用者負担額）については全額自己負担分を含むサービス提供票別表から取得する.
     * ただしサービス提供票別表は古い明細書には設定されていない属性のためその場合は従来通り明細書の自己負担額を用いる.
     *
     * サービス提供票別表が含まれない古い明細書については支給限度基準を超える単位数に対応する以前のものであり
     * 全額自己負担分が0円, つまり明細書の自己負担額が正しい金額となる.
     *
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @return int
     */
    private static function computeCopayAmount(LtcsBillingStatement $statement): int
    {
        return $statement->appendix?->copayAmount ?? $statement->insurance->copayAmount;
    }

    /**
     * 医療費控除額を計算する.
     *
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @param int $copayAmount
     * @return int 医療費控除額
     */
    private static function computeMedicalDeductionAmount(LtcsBillingStatement $statement, int $copayAmount): int
    {
        $items = Seq::from(...$statement->items)
            ->filter(function (LtcsBillingStatementItem $x): bool {
                return $x->serviceCodeCategory->isHomeVisitLongTermCare();
            })
            ->computed();
        $itemsWithPhysicalCare = $items
            ->filter(function (LtcsBillingStatementItem $x): bool {
                return $x->serviceCodeCategory->isHomeVisitLongTermCareWithPhysicalCare();
            })
            ->computed();

        $totalScore = $items
            ->map(fn (LtcsBillingStatementItem $x): int => $x->totalScore)
            ->sum();
        $totalScoreWithPhysicalCare = $itemsWithPhysicalCare
            ->map(fn (LtcsBillingStatementItem $x): int => $x->totalScore)
            ->sum();

        return Math::floor($copayAmount * $totalScoreWithPhysicalCare / $totalScore);
    }
}
