<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\ConsumptionTaxRate;
use Domain\Model;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 利用者請求：障害福祉サービス明細.
 *
 * @property-read int $dwsStatementId 障害福祉明細書ID
 * @property-read int $score 単位数
 * @property-read \Domain\Common\Decimal $unitCost 単価
 * @property-read int $subtotalCost 小計
 * @property-read \Domain\Common\ConsumptionTaxRate $tax 消費税
 * @property-read int $medicalDeductionAmount 医療費控除対象額
 * @property-read int $benefitAmount 介護給付額
 * @property-read int $subsidyAmount 自治体助成額
 * @property-read int $totalAmount 合計
 * @property-read int $copayWithoutTax 自己負担額（税抜）
 * @property-read int $copayWithTax 自己負担額（税込）
 */
final class UserBillingDwsItem extends Model
{
    /**
     * 利用者請求：障害福祉サービス明細 ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return static
     */
    public static function from(DwsBillingStatement $statement): self
    {
        $subtotalCost = $statement->totalFee;
        $benefitAmount = $statement->totalBenefit;
        $subsidyAmount = $statement->totalSubsidy;
        $totalAmount = $subtotalCost - $benefitAmount - $subsidyAmount;

        return self::create([
            'dwsStatementId' => $statement->id,
            'score' => $statement->totalScore,
            'unitCost' => $statement->aggregates[0]->unitCost,
            'subtotalCost' => $subtotalCost,
            'tax' => ConsumptionTaxRate::zero(),
            'medicalDeductionAmount' => self::computeMedicalDeductionAmount($statement),
            'benefitAmount' => $benefitAmount,
            'subsidyAmount' => $subsidyAmount,
            'totalAmount' => $totalAmount,
            // 非課税のため税込・税抜は金額と同じ。
            'copayWithoutTax' => $totalAmount,
            'copayWithTax' => $totalAmount,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'dwsStatementId',
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
            'dwsStatementId' => true,
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
     * 医療費控除額を計算する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return int 医療費控除額
     */
    private static function computeMedicalDeductionAmount(DwsBillingStatement $statement): int
    {
        return self::computeHomeHelpServiceMedicalDeductionAmount($statement)
            + self::computeVisitingCareForPwsdMedicalDeductionAmount($statement);
    }

    /**
     * 居宅介護の医療費控除額を計算する.
     *
     * 居宅介護の場合は身体介護（を伴うサービス）が医療費控除の対象となる.
     * そのため「身体介護（を伴うサービス）の単位数の割合」を求め、利用者負担額に乗じることで医療費控除対象額を算出する.
     *
     * TODO: 初回加算や上限管理加算について再度検討する必要があるかもしれない
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return int 医療費控除額
     */
    private static function computeHomeHelpServiceMedicalDeductionAmount(DwsBillingStatement $statement): int
    {
        $items = Seq::from(...$statement->items)
            ->filter(function (DwsBillingStatementItem $x): bool {
                return $x->serviceCodeCategory->isHomeHelpService();
            })
            ->computed();

        // `$items` が空 = 居宅介護を提供していない場合はもちろん0円
        // ゼロ徐算回避のため早期 return する
        if ($items->isEmpty()) {
            return 0;
        }

        $itemsWithPhysicalCare = $items
            ->filter(function (DwsBillingStatementItem $x): bool {
                return $x->serviceCodeCategory->isHomeHelpServiceWithPhysicalCare();
            })
            ->computed();

        $totalScore = $items
            ->map(fn (DwsBillingStatementItem $x): int => $x->totalScore)
            ->sum();

        // ありえないとは思うけどこの時点で単位数の合計が 0 の場合はゼロ徐算回避のため早期 return する
        if ($totalScore === 0) {
            return 0;
        }

        $totalScoreWithPhysicalCare = $itemsWithPhysicalCare
            ->map(fn (DwsBillingStatementItem $x): int => $x->totalScore)
            ->sum();

        $copay = self::getCopayFromStatement($statement, DwsServiceDivisionCode::homeHelpService());
        return Math::floor($copay * $totalScoreWithPhysicalCare / $totalScore);
    }

    /**
     * 重度訪問介護の医療費控除対象額を計算する.
     *
     * 重度訪問介護の場合は「利用者負担額 × 1/2」が医療費控除対象額となる.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return int 医療費控除額
     */
    private static function computeVisitingCareForPwsdMedicalDeductionAmount(DwsBillingStatement $statement): int
    {
        $copay = self::getCopayFromStatement($statement, DwsServiceDivisionCode::visitingCareForPwsd());
        return Math::floor($copay / 2);
    }

    /**
     * 明細書から指定したサービス種類の利用者負担額を取得する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @return int
     */
    private static function getCopayFromStatement(
        DwsBillingStatement $statement,
        DwsServiceDivisionCode $serviceDivisionCode
    ): int {
        $x = Seq::fromArray($statement->aggregates)
            ->filter(fn (DwsBillingStatementAggregate $x): bool => $x->serviceDivisionCode === $serviceDivisionCode)
            ->map(fn (DwsBillingStatementAggregate $x): int => $x->subtotalCopay - $x->subtotalSubsidy)
            ->sum();
        // ありえ無いはずだが念の為マイナスになったときのために最低でも0となるようにしておく.
        return max($x, 0);
    }
}
