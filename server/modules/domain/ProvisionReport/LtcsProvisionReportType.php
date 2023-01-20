<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Enum;

/**
 * 介護保険サービス：予実区分.
 *
 * @method static LtcsProvisionReportType homeVisitLongTermCare() 訪問介護
 * @method static LtcsProvisionReportType comprehensiveService() 総合事業
 */
final class LtcsProvisionReportType extends Enum
{
    use LtcsProvisionReportTypeSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'homeVisitLongTermCare' => 1,
        'comprehensiveService' => 2,
    ];
}
