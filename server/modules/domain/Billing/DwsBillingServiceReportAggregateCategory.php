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
 * サービス提供実績記録票：合計区分カテゴリー.
 *
 * @method static DwsBillingServiceReportAggregateCategory category100() 内訳 100%
 * @method static DwsBillingServiceReportAggregateCategory category90() 内訳 90%
 * @method static DwsBillingServiceReportAggregateCategory category70() 内訳 70%
 * @method static DwsBillingServiceReportAggregateCategory categoryPwsd() 内訳 重訪
 * @method static DwsBillingServiceReportAggregateCategory categoryTotal() 合計 算定時間数計
 */
final class DwsBillingServiceReportAggregateCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'category100' => 1,
        'category90' => 2,
        'category70' => 3,
        'categoryPwsd' => 4,
        'categoryTotal' => 5,
    ];
}
