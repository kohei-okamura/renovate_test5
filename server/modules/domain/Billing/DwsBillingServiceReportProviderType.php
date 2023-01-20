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
 * サービス提供実績記録票：ヘルパー資格.
 *
 * @method static DwsBillingServiceReportProviderType none() 未設定
 * @method static DwsBillingServiceReportProviderType novice() 初任者等
 * @method static DwsBillingServiceReportProviderType beginner() 基礎等
 * @method static DwsBillingServiceReportProviderType careWorkerForPwsd() 重訪
 */
final class DwsBillingServiceReportProviderType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'novice' => 11,
        'beginner' => 12,
        'careWorkerForPwsd' => 13,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        0 => '未設定',
        11 => '初任者等',
        12 => '基礎等',
        13 => '重訪',
    ];

    /**
     * Resolve DwsBillingServiceReportProviderType to label.
     *
     * @param \Domain\Billing\DwsBillingServiceReportProviderType $x
     * @return string
     */
    public static function resolve(DwsBillingServiceReportProviderType $x): string
    {
        return self::$map[$x->value()];
    }
}
