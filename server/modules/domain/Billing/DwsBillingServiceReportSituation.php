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
 * サービス提供実績記録票：サービス提供の状況.
 *
 * @method static DwsBillingServiceReportSituation none() 未設定
 * @method static DwsBillingServiceReportSituation hospitalized() 入院
 * @method static DwsBillingServiceReportSituation longHospitalized() 入院（長期）
 */
final class DwsBillingServiceReportSituation extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'hospitalized' => 1,
        'longHospitalized' => 2,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        0 => '未設定',
        1 => '入院',
        2 => '入院（長期）',
    ];

    /**
     * Resolve DwsBillingServiceReportSituation to label.
     *
     * @param \Domain\Billing\DwsBillingServiceReportSituation $x
     * @return string
     */
    public static function resolve(DwsBillingServiceReportSituation $x): string
    {
        return self::$map[$x->value()];
    }
}
