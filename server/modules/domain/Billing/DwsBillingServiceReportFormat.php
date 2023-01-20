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
 * サービス提供実績記録票：様式種別番号.
 *
 * @method static DwsBillingServiceReportFormat homeHelpService() 様式1（居宅介護サービス提供実績記録票情報）
 * @method static DwsBillingServiceReportFormat visitingCareForPwsd() 様式3-1（重度訪問介護サービス提供実績記録票）
 */
final class DwsBillingServiceReportFormat extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'homeHelpService' => '0101',
        'visitingCareForPwsd' => '0301',
    ];
}
