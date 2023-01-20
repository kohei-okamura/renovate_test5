<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * サービス提供実績記録票：合計区分グループ.
 *
 * @method static DwsBillingServiceReportAggregateGroup physicalCare() 居宅介護：合計1「身体介護」
 * @method static DwsBillingServiceReportAggregateGroup accompanyWithPhysicalCare() 居宅介護：合計2「通院等介助（身体を伴う）」
 * @method static DwsBillingServiceReportAggregateGroup housework() 居宅介護：合計3「家事援助」
 * @method static DwsBillingServiceReportAggregateGroup accompany() 居宅介護：合計4「通院等介助（身体を伴わない）」
 * @method static DwsBillingServiceReportAggregateGroup accessibleTaxi() 居宅介護：合計5「通院等乗降介助」
 * @method static DwsBillingServiceReportAggregateGroup visitingCareForPwsd() 重度訪問介護
 * @method static DwsBillingServiceReportAggregateGroup outingSupportForPwsd() 重度訪問介護：移動介護分
 */
final class DwsBillingServiceReportAggregateGroup extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'physicalCare' => 11,
        'accompanyWithPhysicalCare' => 12,
        'housework' => 13,
        'accompany' => 14,
        'accessibleTaxi' => 15,
        'visitingCareForPwsd' => 21,
        'outingSupportForPwsd' => 22,
    ];
}
