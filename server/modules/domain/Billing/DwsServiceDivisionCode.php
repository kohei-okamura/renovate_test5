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
 * 障害福祉サービス：請求：サービス種類コード.
 *
 * @method static DwsServiceDivisionCode homeHelpService() 居宅介護
 * @method static DwsServiceDivisionCode visitingCareForPwsd() 重度訪問介護
 */
final class DwsServiceDivisionCode extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'homeHelpService' => '11',
        'visitingCareForPwsd' => '12',
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        '11' => '居宅介護',
        '12' => '重度訪問介護',
    ];

    /**
     * Resolve DwsServiceDivisionCode to label.
     *
     * @param \Domain\Billing\DwsServiceDivisionCode $x
     * @return string
     */
    public static function resolve(DwsServiceDivisionCode $x): string
    {
        return self::$map[$x->value()];
    }
}
