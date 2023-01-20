<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 障害：サービス提供実績記録票 PDF 合計（算定時間数）
 */
final class DwsBillingServiceReportPdfResult extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingServiceReportPdfDuration} constructor.
     *
     * @param string $physicalCare 居宅介護：身体（合計）
     * @param string $physicalCare100 居宅介護：身体（内訳 100%）
     * @param string $physicalCare70 居宅介護：身体（内訳 70%）
     * @param string $physicalCarePwsd 居宅介護：身体（内訳 重訪）
     * @param string $accompanyWithPhysicalCare 居宅介護：通院等介助（身体を伴う)（合計）
     * @param string $accompanyWithPhysicalCare100 居宅介護：通院等介助（身体を伴う)（内訳 100%）
     * @param string $accompanyWithPhysicalCare70 居宅介護：通院等介助（身体を伴う)（内訳 70%）
     * @param string $accompanyWithPhysicalCarePwsd 居宅介護：通院等介助（身体を伴う)（内訳 重訪）
     * @param string $housework 居宅介護：家事援助（合計）
     * @param string $housework100 居宅介護：家事援助（内訳 100%）
     * @param string $housework90 居宅介護：家事援助（内訳 90%）
     * @param string $houseworkPwsd 居宅介護：家事援助（内訳 重訪）
     * @param string $accompany 居宅介護：通院等介助（身体を伴わない）（合計）
     * @param string $accompany100 居宅介護：通院等介助（身体を伴わない）（内訳 100%）
     * @param string $accompany90 居宅介護：通院等介助（身体を伴わない）（内訳 90%）
     * @param string $accompanyPwsd 居宅介護：通院等介助（身体を伴わない）（内訳 重訪）
     * @param string $accessibleTaxi 居宅介護：通院等乗降介助 （合計）
     * @param string $accessibleTaxi100 居宅介護：通院等乗降介助 （内訳 100%）
     * @param string $accessibleTaxi90 居宅介護：通院等乗降介助 （内訳 90%）
     * @param string $accessibleTaxiPwsd 居宅介護：通院等乗降介助 （内訳 重訪）
     * @param string $visitingCareForPwsd 重度訪問介護
     * @param string $outingSupportForPwsd 重度訪問介護：移動介護分
     */
    public function __construct(
        public readonly string $physicalCare,
        public readonly string $physicalCare100,
        public readonly string $physicalCare70,
        public readonly string $physicalCarePwsd,
        public readonly string $accompanyWithPhysicalCare,
        public readonly string $accompanyWithPhysicalCare100,
        public readonly string $accompanyWithPhysicalCare70,
        public readonly string $accompanyWithPhysicalCarePwsd,
        public readonly string $housework,
        public readonly string $housework100,
        public readonly string $housework90,
        public readonly string $houseworkPwsd,
        public readonly string $accompany,
        public readonly string $accompany100,
        public readonly string $accompany90,
        public readonly string $accompanyPwsd,
        public readonly string $accessibleTaxi,
        public readonly string $accessibleTaxi100,
        public readonly string $accessibleTaxi90,
        public readonly string $accessibleTaxiPwsd,
        public readonly string $visitingCareForPwsd,
        public readonly string $outingSupportForPwsd,
    ) {
    }

    /**
     * サービス提供実績記録票：合計 を PDF に描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $result
     * @return array
     */
    public static function from(DwsBillingServiceReportAggregate $result): self
    {
        $physicalCare = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $physicalCare100 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::category100()
            )
            ->toFloat();
        $physicalCare70 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::category70()
            )
            ->toFloat();
        $physicalCarePwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            )
            ->toFloat();
        $accompanyWithPhysicalCare = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accompanyWithPhysicalCare100 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::category100()
            )
            ->toFloat();
        $accompanyWithPhysicalCare70 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::category70()
            )
            ->toFloat();
        $accompanyWithPhysicalCarePwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            )
            ->toFloat();
        $housework = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $housework100 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::category100()
            )
            ->toFloat();
        $housework90 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::category90()
            )
            ->toFloat();
        $houseworkPwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accompany = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accompany100 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::category100()
            )
            ->toFloat();
        $accompany90 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::category90()
            )
            ->toFloat();
        $accompanyPwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            )
            ->toFloat();
        $accessibleTaxi = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accessibleTaxi100 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::category100()
            )
            ->toFloat();
        $accessibleTaxi90 = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::category90()
            )
            ->toFloat();
        $accessibleTaxiPwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            )
            ->toFloat();
        $visitingCareForPwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $outingSupportForPwsd = $result
            ->get(
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        return new self(
            physicalCare: $physicalCare > 0 ? (string)$physicalCare : '',
            physicalCare100: $physicalCare100 > 0 ? (string)$physicalCare100 : '',
            physicalCare70: $physicalCare70 > 0 ? (string)$physicalCare70 : '',
            physicalCarePwsd: $physicalCarePwsd > 0 ? (string)$physicalCarePwsd : '',
            accompanyWithPhysicalCare: $accompanyWithPhysicalCare > 0 ? (string)$accompanyWithPhysicalCare : '',
            accompanyWithPhysicalCare100: $accompanyWithPhysicalCare100 > 0
                ? (string)$accompanyWithPhysicalCare100
                : '',
            accompanyWithPhysicalCare70: $accompanyWithPhysicalCare70 > 0
                ? (string)$accompanyWithPhysicalCare70
                : '',
            accompanyWithPhysicalCarePwsd: $accompanyWithPhysicalCarePwsd > 0
                ? (string)$accompanyWithPhysicalCarePwsd
                : '',
            housework: $housework > 0 ? (string)$housework : '',
            housework100: $housework100 > 0 ? (string)$housework100 : '',
            housework90: $housework90 > 0 ? (string)$housework90 : '',
            houseworkPwsd: $houseworkPwsd > 0 ? (string)$houseworkPwsd : '',
            accompany: $accompany > 0 ? (string)$accompany : '',
            accompany100: $accompany100 > 0 ? (string)$accompany100 : '',
            accompany90: $accompany90 > 0 ? (string)$accompany90 : '',
            accompanyPwsd: $accompanyPwsd > 0 ? (string)$accompanyPwsd : '',
            accessibleTaxi: $accessibleTaxi > 0 ? (string)$accessibleTaxi : '',
            accessibleTaxi100: $accessibleTaxi100 > 0 ? (string)$accessibleTaxi100 : '',
            accessibleTaxi90: $accessibleTaxi90 > 0 ? (string)$accessibleTaxi90 : '',
            accessibleTaxiPwsd: $accessibleTaxiPwsd > 0 ? (string)$accessibleTaxiPwsd : '',
            visitingCareForPwsd: $visitingCareForPwsd > 0 ? (string)$visitingCareForPwsd : '',
            outingSupportForPwsd: $outingSupportForPwsd > 0 ? (string)$outingSupportForPwsd : '',
        );
    }
}
