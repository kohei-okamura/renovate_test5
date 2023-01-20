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
 * 介護保険サービス：請求：サービス種類コード.
 *
 * @method static LtcsServiceDivisionCode homeVisitLongTermCare() 訪問介護
 */
final class LtcsServiceDivisionCode extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'homeVisitLongTermCare' => '11',
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        '11' => '訪問介護',
    ];

    /**
     * Resolve LtcsServiceDivisionCode to label.
     *
     * @param \Domain\Billing\LtcsServiceDivisionCode $x
     * @return string
     */
    public static function resolve(LtcsServiceDivisionCode $x): string
    {
        return self::$map[$x->value()];
    }
}
