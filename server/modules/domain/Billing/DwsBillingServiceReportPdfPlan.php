<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Polite;

/**
 * 障害：サービス提供実績記録票 PDF 合計（計画時間数）
 */
final class DwsBillingServiceReportPdfPlan extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingServiceReportPdfDuration} constructor.
     *
     * @param string $physicalCare 居宅介護：身体
     * @param string $accompanyWithPhysicalCare 居宅介護：通院等介助（身体を伴う）
     * @param string $housework 居宅介護：家事援助
     * @param string $accompany 居宅介護：通院等介助（身体を伴わない）
     * @param string $accessibleTaxi 通院等乗降介助
     * @param string $visitingCareForPwsd 重度訪問介護
     * @param string $outingSupportForPwsd 重度訪問介護：移動介護分
     */
    public function __construct(
        public readonly string $physicalCare,
        public readonly string $accompanyWithPhysicalCare,
        public readonly string $housework,
        public readonly string $accompany,
        public readonly string $accessibleTaxi,
        public readonly string $visitingCareForPwsd,
        public readonly string $outingSupportForPwsd,
    ) {
    }

    /**
     * サービス提供実績記録票：合計 を PDF に描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $plan
     * @return array
     */
    public static function from(DwsBillingServiceReportAggregate $plan): self
    {
        $physicalCare = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accompanyWithPhysicalCare = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $housework = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accompany = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $accessibleTaxi = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $visitingCareForPwsd = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        $outingSupportForPwsd = $plan
            ->get(
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            ->toFloat();
        return new self(
            physicalCare: $physicalCare > 0 ? (string)$physicalCare : '',
            accompanyWithPhysicalCare: $accompanyWithPhysicalCare > 0 ? (string)$accompanyWithPhysicalCare : '',
            housework: $housework > 0 ? (string)$housework : '',
            accompany: $accompany > 0 ? (string)$accompany : '',
            accessibleTaxi: $accessibleTaxi > 0 ? (string)$accessibleTaxi : '',
            visitingCareForPwsd: $visitingCareForPwsd > 0 ? (string)$visitingCareForPwsd : '',
            outingSupportForPwsd: $outingSupportForPwsd > 0 ? (string)$outingSupportForPwsd : ''
        );
    }
}
