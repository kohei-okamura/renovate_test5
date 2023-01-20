<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\Polite;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry as AppendixEntry;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票別表.
 */
final class LtcsProvisionReportSheetAppendix extends Polite
{
    /** @var \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry 合計行 */
    public readonly LtcsProvisionReportSheetAppendixEntry $managedTotalEntry;

    /**
     * @var int サービス単位数/金額（合計）
     *
     * **FYI**
     * 支給限度額対象の単位数のみの合計なので支給限度額対象外の単位数も含めた合計は
     * `getTotalWholeScore` メソッドを用いること
     */
    public readonly int $totalScoreTotal;

    /** @var int 区分支給限度基準を超える単位数（合計） */
    public readonly int $maxBenefitExcessScoreTotal;

    /** @var int 区分支給限度基準内単位数（合計） */
    public readonly int $scoreWithinMaxBenefitTotal;

    /** @var int 費用総額(保険/事業対象分)（合計） */
    public readonly int $totalFeeForInsuranceOrBusinessTotal;

    /** @var int 保険/事業費請求額（合計） */
    public readonly int $claimAmountForInsuranceOrBusinessTotal;

    /** @var int 利用者負担(保険/事業対象分)（合計） */
    public readonly int $copayForInsuranceOrBusinessTotal;

    /** @var int 利用者負担(全額負担分)（合計） */
    public readonly int $copayWholeExpenseTotal;

    /**
     * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $insNumber 被保険者証番号
     * @param string $userName 利用者氏名
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $managedEntries サービス情報（支給限度対象）
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $unmanagedEntries サービス情報（支給限度対象外）
     * @param int $maxBenefit 区分支給限度基準額（単位）
     * @param int $insuranceClaimAmount 保険請求分
     * @param int $subsidyClaimAmount 公費請求額
     * @param int $copayAmount 利用者請求額
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     */
    public function __construct(
        public readonly Carbon $providedIn,
        public readonly string $insNumber,
        public readonly string $userName,
        public readonly Seq $unmanagedEntries,
        public readonly Seq $managedEntries,
        public readonly int $maxBenefit,
        public readonly int $insuranceClaimAmount,
        public readonly int $subsidyClaimAmount,
        public readonly int $copayAmount,
        public readonly Decimal $unitCost
    ) {
        $managedTotalEntry = LtcsProvisionReportSheetAppendixEntry::computeTotal($managedEntries);

        $this->managedTotalEntry = $managedTotalEntry;
        $this->totalScoreTotal = $managedTotalEntry->wholeScore;
        $this->maxBenefitExcessScoreTotal = $managedTotalEntry->maxBenefitExcessScore;
        $this->scoreWithinMaxBenefitTotal = $managedTotalEntry->scoreWithinMaxBenefit;

        $this->totalFeeForInsuranceOrBusinessTotal =
            $managedTotalEntry->totalFeeForInsuranceOrBusiness
            + $unmanagedEntries->map(fn (AppendixEntry $x): int => $x->totalFeeForInsuranceOrBusiness)->sum();

        $this->claimAmountForInsuranceOrBusinessTotal =
            $managedTotalEntry->claimAmountForInsuranceOrBusiness
            + $unmanagedEntries->map(fn (AppendixEntry $x): int => $x->claimAmountForInsuranceOrBusiness)->sum();

        $this->copayForInsuranceOrBusinessTotal =
            $managedTotalEntry->copayForInsuranceOrBusiness
            + $unmanagedEntries->map(fn (AppendixEntry $x): int => $x->copayForInsuranceOrBusiness)->sum();

        $this->copayWholeExpenseTotal =
            $managedTotalEntry->copayWholeExpense
            + $unmanagedEntries->map(fn (AppendixEntry $x): int => $x->copayWholeExpense)->sum();
    }

    /**
     * 合計単位数を取得する.
     *
     * 属性 `totalScoreTotal` は紙のサービス提供票別表「サービス単位数/金額」の合計値として印字される値を持っているが
     * これには支給限度額対象の単位数しか含まれていないため, 支給限度額対象外の単位数も含めた合計値を得るために
     * このメソッドを用いる.
     *
     * このメソッドが返す値には種類支給限度基準を超える単位数や区分支給限度基準を超える単位数も含まれる.
     *
     * @return int
     */
    public function getTotalWholeScore(): int
    {
        $managedScore = $this->managedTotalEntry->wholeScore;
        $unmanagedScore = $this->unmanagedEntries->map(fn (AppendixEntry $x): int => $x->wholeScore)->sum();
        return $managedScore + $unmanagedScore;
    }

    /**
     * 介護保険サービス：サービス提供票別表 を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report 予実
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth 月初時点の介護保険被保険者証
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth 月末時点の介護保険被保険者証
     * @param \Domain\Office\Office $office 事業所
     * @param \Domain\User\User $user 利用者
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails サービス詳細
     * @param int $insuranceClaimAmount 保険請求額
     * @param int $subsidyClaimAmount 公費請求額
     * @param int $copayAmount 利用者請求額
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param \ScalikePHP\Map&string[] $serviceCodeMap [サービスコード => サービス名称]
     * @return $this
     */
    public static function from(
        LtcsProvisionReport $report,
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Office $office,
        User $user,
        Seq $serviceDetails,
        int $insuranceClaimAmount,
        int $subsidyClaimAmount,
        int $copayAmount,
        Decimal $unitCost,
        Map $serviceCodeMap,
    ): self {
        /** @var \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $managed */
        /** @var \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $unmanaged */
        [$managed, $unmanaged] = Seq::fromArray($serviceDetails)
            ->partition(fn (LtcsBillingServiceDetail $x): bool => $x->isLimited);
        $benefitRate = 100 - $insCardAtLastOfMonth->copayRate;

        $managedEntries = $managed
            ->groupBy(fn (LtcsBillingServiceDetail $x): string => $x->serviceCode->toString() . '#' . $x->unitScore)
            ->values()
            ->sortBy(function (Seq $xs): string {
                /** @var \Domain\Billing\LtcsBillingServiceDetail $x */
                $x = $xs->head();
                return $x->serviceCode->toString();
            })
            ->map(fn (Seq $xs): AppendixEntry => LtcsProvisionReportSheetAppendixEntry::from(
                $benefitRate,
                $unitCost,
                $office,
                $serviceCodeMap,
                $xs,
                $report->result->maxBenefitQuotaExcessScore,
                $report->result->maxBenefitExcessScore,
            ))
            ->computed();
        $unmanagedEntries = $unmanaged
            ->map(fn (LtcsBillingServiceDetail $x): AppendixEntry => LtcsProvisionReportSheetAppendixEntry::from(
                $benefitRate,
                $unitCost,
                $office,
                $serviceCodeMap,
                Seq::from($x),
                $x->maxBenefitQuotaExcessScore,
                $x->maxBenefitExcessScore,
            ))
            ->computed();

        $hasBeenChanged = LtcsInsCard::levelHasBeenChanged($insCardAtFirstOfMonth, $insCardAtLastOfMonth);
        return new self(
            providedIn: $report->providedIn,
            insNumber: $insCardAtLastOfMonth->insNumber,
            userName: $user->name->displayName,
            unmanagedEntries: $unmanagedEntries,
            managedEntries: $managedEntries,
            maxBenefit: self::resolveMaxBenefit($insCardAtFirstOfMonth, $insCardAtLastOfMonth, $hasBeenChanged),
            insuranceClaimAmount: $insuranceClaimAmount,
            subsidyClaimAmount: $subsidyClaimAmount,
            copayAmount: $copayAmount,
            unitCost: $unitCost,
        );
    }

    /**
     * 要介護状態区分に対応する区分支給限度基準額を導出する.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonthOption
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth
     * @param bool $hasBeenChanged
     * @return int
     */
    private static function resolveMaxBenefit(
        Option $insCardAtFirstOfMonthOption,
        LtcsInsCard $insCardAtLastOfMonth,
        bool $hasBeenChanged
    ): int {
        // 要介護度が変更されていた場合、より高い要介護状態区分に対応する区分支給限度基準額を使う
        if ($hasBeenChanged) {
            /** @var \Domain\LtcsInsCard\LtcsInsCard $insCardAtFirstOfMonth */
            $insCardAtFirstOfMonth = $insCardAtFirstOfMonthOption->get(); // 要介護度が変わっている場合 $insCardAtFirstOfMonth は必ず some
            return $insCardAtLastOfMonth->greaterThanForLevel($insCardAtFirstOfMonth)
                ? $insCardAtLastOfMonth->ltcsLevel->maxBenefit()
                : $insCardAtFirstOfMonth->ltcsLevel->maxBenefit();
        }
        return $insCardAtLastOfMonth->ltcsLevel->maxBenefit();
    }
}
