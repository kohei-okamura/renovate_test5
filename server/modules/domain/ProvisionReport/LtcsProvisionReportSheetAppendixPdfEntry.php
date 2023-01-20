<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Polite;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：サービス提供票別表PDFサービス情報.
 */
final class LtcsProvisionReportSheetAppendixPdfEntry extends Polite
{
    /**
     * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry} constructor.
     *
     * @param string $officeName 事業所名
     * @param string $officeCode 事業所番号
     * @param string $serviceName サービス内容/種類
     * @param string $serviceCode サービスコード
     * @param string $unitScore 単位数
     * @param string $count 回数
     * @param string $wholeScore サービス単位数/金額
     * @param string $maxBenefitQuotaExcessScore 種類支給限度基準を超える単位数
     * @param string $maxBenefitExcessScore 区分支給限度基準を超える単位数
     * @param string $scoreWithinMaxBenefitQuota 種類支給限度基準内単位数
     * @param string $scoreWithinMaxBenefit 区分支給限度基準内単位数
     * @param string $unitCost 単位数単価
     * @param string $totalFeeForInsuranceOrBusiness 費用総額(保険/事業対象分)
     * @param string $benefitRate 給付率(%)
     * @param string $claimAmountForInsuranceOrBusiness 保険/事業費請求額
     * @param string $copayForInsuranceOrBusiness 利用者負担(保険/事業対象分)
     * @param string $copayWholeExpense 利用者負担(全額負担分)
     */
    public function __construct(
        public readonly string $officeName,
        public readonly string $officeCode,
        public readonly string $serviceName,
        public readonly string $serviceCode,
        public readonly string $unitScore,
        public readonly string $count,
        public readonly string $wholeScore,
        public readonly string $maxBenefitQuotaExcessScore,
        public readonly string $maxBenefitExcessScore,
        public readonly string $scoreWithinMaxBenefitQuota,
        public readonly string $scoreWithinMaxBenefit,
        public readonly string $unitCost,
        public readonly string $totalFeeForInsuranceOrBusiness,
        public readonly string $benefitRate,
        public readonly string $claimAmountForInsuranceOrBusiness,
        public readonly string $copayForInsuranceOrBusiness,
        public readonly string $copayWholeExpense,
    ) {
    }

    /**
     * 介護保険サービス：サービス提供票別表PDFサービス情報一覧を生成する.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $managedEntries
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq $unmanagedEntries
     * @param \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry $totalEntry
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]&\ScalikePHP\Seq
     */
    public static function from(Seq $managedEntries, Seq $unmanagedEntries, LtcsProvisionReportSheetAppendixEntry $totalEntry): Seq
    {
        $managed = $managedEntries->count() > 1
            ? $managedEntries->map(
                fn (LtcsProvisionReportSheetAppendixEntry $x): LtcsProvisionReportSheetAppendixPdfEntry => new self(
                    officeName: $x->officeName,
                    officeCode: $x->officeCode,
                    serviceName: $x->serviceName,
                    serviceCode: $x->serviceCode,
                    unitScore: number_format($x->unitScore),
                    count: number_format($x->count),
                    wholeScore: number_format($x->wholeScore),
                    maxBenefitQuotaExcessScore: '',
                    maxBenefitExcessScore: '',
                    scoreWithinMaxBenefitQuota: '',
                    scoreWithinMaxBenefit: '',
                    unitCost: '',
                    totalFeeForInsuranceOrBusiness: '',
                    benefitRate: '',
                    claimAmountForInsuranceOrBusiness: '',
                    copayForInsuranceOrBusiness: '',
                    copayWholeExpense: '',
                )
            )
            : $managedEntries->map(
                fn (LtcsProvisionReportSheetAppendixEntry $x): LtcsProvisionReportSheetAppendixPdfEntry => new self(
                    officeName: $x->officeName,
                    officeCode: $x->officeCode,
                    serviceName: $x->serviceName,
                    serviceCode: $x->serviceCode,
                    unitScore: number_format($x->unitScore),
                    count: number_format($x->count),
                    wholeScore: number_format($x->wholeScore),
                    maxBenefitQuotaExcessScore: $x->maxBenefitQuotaExcessScore > 0
                        ? number_format($x->maxBenefitQuotaExcessScore)
                        : '',
                    maxBenefitExcessScore: $x->maxBenefitExcessScore > 0
                        ? number_format($x->maxBenefitExcessScore)
                        : '',
                    scoreWithinMaxBenefitQuota: $x->maxBenefitQuotaExcessScore > 0
                        ? number_format($x->scoreWithinMaxBenefitQuota)
                        : '',
                    scoreWithinMaxBenefit: number_format($x->scoreWithinMaxBenefit),
                    unitCost: sprintf('%.2f', $x->unitCost->toFloat()),
                    totalFeeForInsuranceOrBusiness: number_format($x->totalFeeForInsuranceOrBusiness),
                    benefitRate: number_format($x->benefitRate),
                    claimAmountForInsuranceOrBusiness: number_format($x->claimAmountForInsuranceOrBusiness),
                    copayForInsuranceOrBusiness: number_format($x->copayForInsuranceOrBusiness),
                    copayWholeExpense: number_format($x->copayWholeExpense),
                )
            );

        $total = $managedEntries->count() > 1
            ? Seq::from(new self(
                officeName: $totalEntry->officeName,
                officeCode: $totalEntry->officeCode,
                serviceName: $totalEntry->serviceName,
                serviceCode: '',
                unitScore: '',
                count: '',
                wholeScore: sprintf('(%s)', number_format($totalEntry->wholeScore)),
                maxBenefitQuotaExcessScore: $totalEntry->maxBenefitQuotaExcessScore > 0
                    ? number_format($totalEntry->maxBenefitQuotaExcessScore)
                    : '',
                maxBenefitExcessScore: $totalEntry->maxBenefitExcessScore > 0
                    ? number_format($totalEntry->maxBenefitExcessScore)
                    : '',
                scoreWithinMaxBenefitQuota: $totalEntry->maxBenefitQuotaExcessScore > 0
                    ? number_format($totalEntry->scoreWithinMaxBenefitQuota)
                    : '',
                scoreWithinMaxBenefit: number_format($totalEntry->scoreWithinMaxBenefit),
                unitCost: sprintf('%.2f', $totalEntry->unitCost->toFloat()),
                totalFeeForInsuranceOrBusiness: number_format($totalEntry->totalFeeForInsuranceOrBusiness),
                benefitRate: number_format($totalEntry->benefitRate),
                claimAmountForInsuranceOrBusiness: number_format($totalEntry->claimAmountForInsuranceOrBusiness),
                copayForInsuranceOrBusiness: number_format($totalEntry->copayForInsuranceOrBusiness),
                copayWholeExpense: number_format($totalEntry->copayWholeExpense),
            ))
            : Seq::empty();
        $unmanaged = $unmanagedEntries->map(
            fn (LtcsProvisionReportSheetAppendixEntry $x): LtcsProvisionReportSheetAppendixPdfEntry => new self(
                officeName: $x->officeName,
                officeCode: $x->officeCode,
                serviceName: $x->serviceName,
                serviceCode: $x->serviceCode,
                unitScore: number_format($x->unitScore),
                count: number_format($x->count),
                wholeScore: sprintf('(%s)', number_format($x->wholeScore)),
                maxBenefitQuotaExcessScore: $x->maxBenefitQuotaExcessScore > 0
                    ? sprintf('(%s)', number_format($x->maxBenefitQuotaExcessScore))
                    : '',
                maxBenefitExcessScore: $x->maxBenefitExcessScore > 0
                    ? sprintf('(%s)', number_format($x->maxBenefitExcessScore))
                    : '',
                scoreWithinMaxBenefitQuota: $x->maxBenefitQuotaExcessScore > 0
                    ? sprintf('(%s)', number_format($x->scoreWithinMaxBenefitQuota))
                    : '',
                scoreWithinMaxBenefit: sprintf('(%s)', number_format($x->scoreWithinMaxBenefit)),
                unitCost: sprintf('%.2f', $x->unitCost->toFloat()),
                totalFeeForInsuranceOrBusiness: number_format($x->totalFeeForInsuranceOrBusiness),
                benefitRate: number_format($x->benefitRate),
                claimAmountForInsuranceOrBusiness: number_format($x->claimAmountForInsuranceOrBusiness),
                copayForInsuranceOrBusiness: number_format($x->copayForInsuranceOrBusiness),
                copayWholeExpense: number_format($x->copayWholeExpense),
            )
        );
        return Seq::fromArray([...$managed, ...$total, ...$unmanaged]);
    }
}
