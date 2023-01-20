<?php
/*
 * Copyright © 2022.  EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\User\User;
use Lib\Exceptions\SetupException;
use Lib\Math;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase;
use UseCase\User\IdentifyUserLtcsSubsidyUseCase;

/**
 * 介護保険サービス：サービス提供票別表組み立てユースケース実装.
 */
final class BuildLtcsProvisionReportSheetAppendixInteractor implements BuildLtcsProvisionReportSheetAppendixUseCase
{
    /**
     * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixInteractor} constructor.
     *
     * @param \UseCase\LtcsAreaGrade\IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase
     * @param \UseCase\User\IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase
     */
    public function __construct(
        private readonly IdentifyLtcsAreaGradeFeeUseCase $identifyLtcsAreaGradeFeeUseCase,
        private readonly IdentifyUserLtcsSubsidyUseCase $identifyUserLtcsSubsidyUseCase,
    ) {
    }

    /**
     * サービス提供票別表ドメインモデルを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report 予実
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth 月初時点の介護保険被保険者証
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth 月末時点の介護保険被保険者証
     * @param \Domain\Office\Office $office 事業所
     * @param \Domain\User\User $user 利用者
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails サービス詳細
     * @param \ScalikePHP\Map&string[] $serviceCodeMap key=サービスコード value=サービス名称
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix
     */
    public function handle(
        Context $context,
        LtcsProvisionReport $report,
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Office $office,
        User $user,
        Seq $serviceDetails,
        Map $serviceCodeMap
    ): LtcsProvisionReportSheetAppendix {
        $unitCost = $this->identifyUnitCost(
            $context,
            $office->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
            $report->providedIn
        );
        $aggregate = $this->buildAggregate(
            context: $context,
            report: $report,
            user: $user,
            serviceDetails: $serviceDetails,
            unitCost: $unitCost,
            copayRate: $insCardAtLastOfMonth->copayRate
        );
        return LtcsProvisionReportSheetAppendix::from(
            report: $report,
            insCardAtFirstOfMonth: $insCardAtFirstOfMonth,
            insCardAtLastOfMonth: $insCardAtLastOfMonth,
            office: $office,
            user: $user,
            serviceDetails: $serviceDetails,
            insuranceClaimAmount: $aggregate->insurance->claimAmount,
            subsidyClaimAmount: Seq::fromArray($aggregate->subsidies)
                ->map(fn (LtcsBillingStatementAggregateSubsidy $x): int => $x->claimAmount)
                ->sum(),
            copayAmount: self::computeCopayAmount($report, $aggregate, $serviceDetails, $unitCost),
            unitCost: $unitCost,
            serviceCodeMap: $serviceCodeMap,
        );
    }

    /**
     * 計算用に介護保険サービス：明細書：集計を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\User\User $user
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails
     * @param \Domain\Common\Decimal $unitCost
     * @param int $copayRate
     * @return \Domain\Billing\LtcsBillingStatementAggregate
     */
    public function buildAggregate(
        Context $context,
        LtcsProvisionReport $report,
        User $user,
        Seq $serviceDetails,
        Decimal $unitCost,
        int $copayRate
    ): LtcsBillingStatementAggregate {
        [$managedScore, $unmanagedScore] = LtcsBillingServiceDetail::aggregateScore(
            details: $serviceDetails,
            excessScore: $report->result->sum()
        );
        $userSubsidies = $this->identifyUserLtcsSubsidyUseCase->handle($context, $user, $report->providedIn);
        return LtcsBillingStatementAggregate::from(
            userSubsidies: $userSubsidies,
            benefitRate: 100 - $copayRate,
            serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
            serviceDays: 0,
            plannedScore: $managedScore,// 今の所生成時は「計画単位数」に「限度額管理対象単位数」と同じ値を用いることとする
            managedScore: $managedScore,
            unmanagedScore: $unmanagedScore,
            unitCost: $unitCost,
        );
    }

    /**
     * 利用者請求額を計算する.
     *
     * - 支給限度基準・種類支給限度基準を超える単位数が両方とも0の場合は明細書の集計の値を利用する.
     * - それ以外の場合は以下の式で組み立てる
     *     - (A) 総サービス単位数合計 = すべてのサービス詳細「総サービス単位数」の合計
     *     - (B) サービス単位数合計 = すべてのサービス詳細「サービス単位数」の合計 - 種類支給限度基準を超える単位数 - 区分支給限度基準を超える単位数
     *     - (C) 費用総額 = (A) 総サービス単位数合計 × 単位数単価（端数は切り捨て）
     *     - (D) 保険/事業費請求額 = 明細書集計欄「保険集計結果」の「請求額」
     *     - (E) 利用者負担（保険/事業対象分）= 明細書集計欄「保険集計結果」の「利用者負担額」
     *     - (F) 利用者請求額 = (C) 費用総額 - (D) 保険/事業費請求額
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\Billing\LtcsBillingStatementAggregate $aggregate
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails
     * @param \Domain\Common\Decimal $fee
     * @return int
     */
    private static function computeCopayAmount(
        LtcsProvisionReport $report,
        LtcsBillingStatementAggregate $aggregate,
        Seq $serviceDetails,
        Decimal $fee
    ): int {
        if ($report->result->maxBenefitExcessScore > 0 || $report->result->maxBenefitQuotaExcessScore > 0) {
            $a = $serviceDetails->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)->sum();
            $c = Math::floor($a * $fee->toFloat());
            $d = $aggregate->insurance->claimAmount;
            return $c - $d;
        } else {
            return $aggregate->insurance->copayAmount;
        }
    }

    /**
     * 介護保険サービス：地域区分単価を特定する.
     *
     * @param \Domain\Context\Context $context
     * @param int $ltcsAreaGradeId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Common\Decimal
     */
    private function identifyUnitCost(
        Context $context,
        int $ltcsAreaGradeId,
        Carbon $providedIn
    ): Decimal {
        return $this->identifyLtcsAreaGradeFeeUseCase
            ->handle($context, $ltcsAreaGradeId, $providedIn)
            ->map(fn (LtcsAreaGradeFee $x): Decimal => $x->fee)
            ->getOrElse(function () use ($ltcsAreaGradeId, $providedIn): void {
                $date = $providedIn->toDateString();
                throw new SetupException("LtcsAreaGradeFee({$ltcsAreaGradeId}/{$date}) not found");
            });
    }
}
