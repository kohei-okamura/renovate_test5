<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Decimal;
use Domain\Office\Office;
use Domain\Polite;
use Lib\Math;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票別表サービス情報.
 */
final class LtcsProvisionReportSheetAppendixEntry extends Polite
{
    // 種類支給限度基準内単位数
    public readonly int $scoreWithinMaxBenefitQuota;
    // 区分支給限度基準内単位数
    public readonly int $scoreWithinMaxBenefit;
    // 費用総額(保険/事業対象分)
    public readonly int $totalFeeForInsuranceOrBusiness;
    // 保険/事業費請求額
    public readonly int $claimAmountForInsuranceOrBusiness;
    // 利用者負担(保険/事業対象分)
    public readonly int $copayForInsuranceOrBusiness;
    // 利用者負担(全額負担分)
    public readonly int $copayWholeExpense;

    /**
     * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry} constructor.
     *
     * @param string $officeName 事業所名
     * @param string $officeCode 事業所番号
     * @param string $serviceName サービス内容/種類
     * @param string $serviceCode サービスコード
     * @param int $unitScore 単位数
     * @param int $count 回数
     * @param int $wholeScore 総単位数
     * @param int $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     * @param int $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param int $benefitRate 給付率(%)
     */
    public function __construct(
        public readonly string $officeName,
        public readonly string $officeCode,
        public readonly string $serviceName,
        public readonly string $serviceCode,
        public readonly int $unitScore,
        public readonly int $count,
        public readonly int $wholeScore,
        public readonly int $maxBenefitQuotaExcessScore,
        public readonly int $maxBenefitExcessScore,
        public readonly Decimal $unitCost,
        public readonly int $benefitRate,
    ) {
        $this->scoreWithinMaxBenefitQuota = $wholeScore - $maxBenefitQuotaExcessScore;
        $this->scoreWithinMaxBenefit = $this->scoreWithinMaxBenefitQuota - $this->maxBenefitExcessScore;
        $this->totalFeeForInsuranceOrBusiness = Math::floor($this->scoreWithinMaxBenefit * $unitCost->toFloat());
        $this->claimAmountForInsuranceOrBusiness = Math::floor($this->totalFeeForInsuranceOrBusiness * $benefitRate / 100);
        $this->copayForInsuranceOrBusiness = $this->totalFeeForInsuranceOrBusiness - $this->claimAmountForInsuranceOrBusiness;
        $this->copayWholeExpense = Math::floor($wholeScore * $unitCost->toFloat()) - $this->totalFeeForInsuranceOrBusiness;
    }

    /**
     * 介護保険サービス：サービス提供票別表PDFサービス情報一覧 を生成する.
     *
     * @param int $benefitRate
     * @param \Domain\Common\Decimal $unitCost
     * @param \Domain\Office\Office $office
     * @param \ScalikePHP\Map&string[] $serviceCodeMap
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param int $maxBenefitQuotaExcessScore
     * @param int $maxBenefitExcessScore
     * @return self
     */
    public static function from(
        int $benefitRate,
        Decimal $unitCost,
        Office $office,
        Map $serviceCodeMap,
        Seq $details,
        int $maxBenefitQuotaExcessScore,
        int $maxBenefitExcessScore
    ): self {
        $detail = $details->head();
        return new self(
            officeName: $office->name,
            officeCode: $office->ltcsHomeVisitLongTermCareService->code,
            serviceName: $serviceCodeMap->getOrElse($detail->serviceCode->toString(), fn (): string => ''),
            serviceCode: $detail->serviceCode->toString(),
            unitScore: $detail->unitScore,
            count: $details->map(fn (LtcsBillingServiceDetail $x): int => $x->count)->sum(),
            wholeScore: $details->map(fn (LtcsBillingServiceDetail $x): int => $x->wholeScore)->sum(),
            maxBenefitQuotaExcessScore: $maxBenefitQuotaExcessScore,
            maxBenefitExcessScore: $maxBenefitExcessScore,
            unitCost: $unitCost,
            benefitRate: $benefitRate,
        );
    }

    /**
     * 介護保険サービス：サービス提供票別表PDFサービス情報 の合計を組み立てる.
     *
     * @param \ScalikePHP\Seq&self[] $entries
     * @return self
     */
    public static function computeTotal(Seq $entries): self
    {
        /** @var self $entry */
        $entry = $entries->head();
        return new self(
            officeName: $entry->officeName,
            officeCode: $entry->officeCode,
            serviceName: '訪問介護 合計',
            serviceCode: '',
            unitScore: 0,
            count: 0,
            wholeScore: $entries->map(fn (self $x): int => $x->wholeScore)->sum(),
            maxBenefitQuotaExcessScore: $entry->maxBenefitQuotaExcessScore,
            maxBenefitExcessScore: $entry->maxBenefitExcessScore,
            unitCost: $entry->unitCost,
            benefitRate: $entry->benefitRate,
        );
    }
}
