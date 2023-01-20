<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use Lib\Exceptions\RuntimeException;
use Lib\Exceptions\SetupException;
use Lib\Math;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス詳細生成サポート
 */
trait ComputeLtcsBillingServiceDetailSupport
{
    /**
     * 加算分のサービス詳細一覧を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param \Domain\Common\Carbon $providedOn
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory[]&\ScalikePHP\Option $category
     * @param int $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     * @param int $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @param int $baseScore 加算対象の合計単位数
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateForCategoryOption(
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        Carbon $providedOn,
        Option $category,
        int $baseScore = 0,
        int $maxBenefitQuotaExcessScore = 0,
        int $maxBenefitExcessScore = 0,
    ): Option {
        return $category->map(
            fn (LtcsServiceCodeCategory $category): LtcsBillingServiceDetail => $this->generateForCategory(
                $report,
                $dictionaryEntries,
                $providedOn,
                $category,
                $maxBenefitQuotaExcessScore,
                $maxBenefitExcessScore,
                $baseScore
            )
        );
    }

    /**
     * 加算分のサービス詳細を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param \Domain\Common\Carbon $providedOn
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $category
     * @param int $maxBenefitQuotaExcessScore
     * @param int $maxBenefitExcessScore
     * @param int $baseScore
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function generateForCategory(
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        Carbon $providedOn,
        LtcsServiceCodeCategory $category,
        int $maxBenefitQuotaExcessScore = 0,
        int $maxBenefitExcessScore = 0,
        int $baseScore = 0
    ): LtcsBillingServiceDetail {
        /** @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry $source */
        $source = $dictionaryEntries
            ->find(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->category === $category)
            ->getOrElse(function () use ($category): void {
                throw new SetupException("LtcsHomeVisitLongTermCareDictionaryEntry(category = {$category}) not found");
            });
        // 現状回数は1回固定なのでunitScore = totalScoreとして計算しておく
        [$totalScore, $wholeScore, $maxBenefitQuotaExcessScore, $maxBenefitExcessScore] = $source->isLimited
            ? (function ($source, $baseScore): array {
                $wholeScore = $this->computeUnitScore($source, 0, 0, $baseScore);
                return [
                    $wholeScore,
                    $wholeScore,
                    0,
                    0,
                ];
            })($source, $baseScore)
            : (function ($a, $b, $c, $source): array {
                // (A) サービス単位数合計 = 引数として受け取る
                // (B) 種類支給限度基準を超える単位数 = 引数として受け取る
                // (C) 区分支給限度基準を超える単位数 = 引数として受け取る
                // ----
                // (D) 総サービス単位数（wholeScore） = (A) × 加算率（端数は四捨五入する）
                // (E) 種類支給限度基準内単位数 = (A - B) × 加算率（端数は四捨五入する）
                // (F) 区分支給限度基準内単位数 = サービス単位数（totalScore） = (A - B - C) × 加算率（端数は四捨五入する）
                // (G) 種類支給限度基準を超える単位数（maxBenefitExcessScore） = D - E
                // (H) 区分支給限度基準を超える単位数（maxBenefitQuotaExcessScore） = E - F
                $wholeScore = $this->computeUnitScore($source, 0, 0, $a);
                $underBenefitQuotaScore = $this->computeUnitScore($source, 0, 0, $a - $b);
                $underBenefitExcessScore = $this->computeUnitScore($source, 0, 0, $a - $b - $c);
                return [
                    $underBenefitExcessScore,
                    $wholeScore,
                    $wholeScore - $underBenefitQuotaScore,
                    $underBenefitQuotaScore - $underBenefitExcessScore,
                ];
            })($baseScore, $maxBenefitQuotaExcessScore, $maxBenefitExcessScore, $source);

        return $this->createServiceDetail(
            $report,
            $providedOn,
            $source->serviceCode,
            $source->category,
            LtcsBuildingSubtraction::none(),
            $source->noteRequirement,
            true,
            $source->isLimited,
            0,
            $totalScore,
            $wholeScore,
            $maxBenefitQuotaExcessScore,
            $maxBenefitExcessScore,
        );
    }

    /**
     * 単位数を算出する.
     *
     * ## 端数について
     * 単位数を算出する際, 各係数を掛けるごとに四捨五入が必要.
     *
     * ## 係数を掛ける順番について
     * 下記の順番で係数を適用する.
     *
     * 1. 夜間早朝加算 or 深夜加算
     * 2. 特定事業所加算
     *
     * 計算途中で四捨五入を行うと順番によって結果が異なるので注意.
     * 「身体介護8」を例に解説すると次の通り.
     *
     * 身体介護8      = 992単位
     * 身体介護8・深  = 1,488単位
     * 身体8・Ⅰ      = 1,190単位
     * 身体8・深・Ⅰ  = 1,786単位
     *
     * - 深夜加算 → 特定事業所加算の順番で計算した場合: 1,488 * 1.2 = 1,786単位（四捨五入） → OK!
     * - 特定事業所加算 → 深夜加算の順番で計算した場合: 1,190 * 1.5 = 1,785単位（四捨五入） → NG...
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry $source
     * @param int $physicalMinutes
     * @param int $headcount
     * @param int $baseScore
     * @return int
     */
    private function computeUnitScore(
        LtcsHomeVisitLongTermCareDictionaryEntry $source,
        int $physicalMinutes,
        int $headcount,
        int $baseScore = 0
    ): int {
        switch ($source->score->calcType) {
            case LtcsCalcType::score():
                // 単位値
                return $source->score->value;
            case LtcsCalcType::baseScore():
                // きざみ基準単位数
                assert($source->extraScore->isAvailable);
                $extra = $source->extraScore;
                /**
                 * サービス時間数 - 時間数がきざみ時間量の倍数、が一致しているときに
                 * 刻み時間数が1多く算定されてしまう問題があるため、1分少なくして調整する
                 *
                 * @see https://docs.google.com/spreadsheets/d/1-L2qveR9OVaGWhVWvGZD89nhvSwu53lwilOL47fHoyY/edit#gid=759467340
                 */
                $diffMinutes = $physicalMinutes - $extra->baseMinutes - 1;
                $diffUnits = Math::floor($diffMinutes / $extra->unitMinutes);
                $baseScore = $source->score->value + $extra->unitScore * $diffUnits;
                $coefficients = Seq::from(
                    $extra->specifiedOfficeAdditionCoefficient,
                    $extra->timeframeAdditionCoefficient
                );
                $score = $coefficients->fold(
                    $baseScore,
                    fn (int $z, int $x): int => Math::round($z * $x / 100)
                );
                return $score * $headcount;
            case LtcsCalcType::percent():
                // %値
                return Math::round($baseScore * $source->score->value / 100);
            case LtcsCalcType::permille():
                // 1/1000値
                return Math::round($baseScore * $source->score->value / 1000);
            default:
                throw new RuntimeException("Unexpected LtcsCalcType({$source->score->calcType})");
        }
    }

    /**
     * 月次で請求する加算等についてサービス詳細を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry&\ScalikePHP\Seq $dictionaryEntries
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $category
     * @param \Domain\Shift\ServiceOption $serviceOption
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Option
     */
    private function generateMonthlyAddition(
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        LtcsServiceCodeCategory $category,
        ServiceOption $serviceOption
    ): Option {
        $p = fn (LtcsProvisionReportEntry $x): bool => $x->hasOption($serviceOption);
        return Seq::from(...$report->entries)->exists($p)
            ? $this->generateForCategoryOption(
                $report,
                $dictionaryEntries,
                $report->providedIn->endOfMonth(),
                Option::from($category)
            )
            : Option::none();
    }

    /**
     * 介護保険サービス：請求：サービス詳細を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\Common\Carbon $providedOn
     * @param \Domain\ServiceCode\ServiceCode $serviceCode
     * @param \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $serviceCodeCategory
     * @param \Domain\ProvisionReport\LtcsBuildingSubtraction $buildingSubtraction
     * @param \Domain\ServiceCodeDictionary\LtcsNoteRequirement $noteRequirement
     * @param bool $isAddition
     * @param bool $isLimited
     * @param int $durationMinutes
     * @param int $unitScore
     * @param int $wholeScore
     * @param int $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     * @param int $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function createServiceDetail(
        LtcsProvisionReport $report,
        Carbon $providedOn,
        ServiceCode $serviceCode,
        LtcsServiceCodeCategory $serviceCodeCategory,
        LtcsBuildingSubtraction $buildingSubtraction,
        LtcsNoteRequirement $noteRequirement,
        bool $isAddition,
        bool $isLimited,
        int $durationMinutes,
        int $unitScore,
        int $wholeScore,
        int $maxBenefitQuotaExcessScore = 0,
        int $maxBenefitExcessScore = 0,
    ): LtcsBillingServiceDetail {
        $userId = $report->userId;
        $count = 1; // 当面の間は固定値
        // TODO: 予実の予定から生成するケースが増えたので正確にはplanがはいってくるケースが増えた。影響はないが（できれば）対応する。
        $disposition = LtcsBillingServiceDetailDisposition::result();
        $totalScore = $unitScore * $count;
        return new LtcsBillingServiceDetail(
            userId: $userId,
            disposition: $disposition,
            providedOn: $providedOn,
            serviceCode: $serviceCode,
            serviceCodeCategory: $serviceCodeCategory,
            buildingSubtraction: $buildingSubtraction,
            noteRequirement: $noteRequirement,
            isAddition: $isAddition,
            isLimited: $isLimited,
            durationMinutes: $durationMinutes,
            unitScore: $unitScore,
            count: $count,
            wholeScore: $wholeScore,
            maxBenefitQuotaExcessScore: $maxBenefitQuotaExcessScore,
            maxBenefitExcessScore: $maxBenefitExcessScore,
            totalScore: $totalScore,
        );
    }
}
