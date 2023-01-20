<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 曜日.
 *
 * @method static DayOfWeek mon() 月
 * @method static DayOfWeek tue() 火
 * @method static DayOfWeek wed() 水
 * @method static DayOfWeek thu() 木
 * @method static DayOfWeek fri() 金
 * @method static DayOfWeek sat() 土
 * @method static DayOfWeek sun() 日
 */
final class DayOfWeek extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
        'sun' => 7,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        1 => '月',
        2 => '火',
        3 => '水',
        4 => '木',
        5 => '金',
        6 => '土',
        7 => '日',
    ];

    /**
     * Resolve DayOfWeek to label.
     *
     * @param \Domain\Common\DayOfWeek $x
     * @return string
     */
    public static function resolve(DayOfWeek $x): string
    {
        return self::$map[$x->value()];
    }
}
