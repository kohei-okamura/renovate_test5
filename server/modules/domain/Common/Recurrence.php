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
 * 繰り返し周期.
 *
 * @method static Recurrence everyWeek() 毎週
 * @method static Recurrence oddWeek() 奇数週
 * @method static Recurrence evenWeek() 偶数週
 * @method static Recurrence firstWeekOfMonth() 第1週
 * @method static Recurrence secondWeekOfMonth() 第2週
 * @method static Recurrence thirdWeekOfMonth() 第3週
 * @method static Recurrence fourthWeekOfMonth() 第4週
 * @method static Recurrence lastWeekOfMonth() 最終週
 */
final class Recurrence extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'everyWeek' => 11,
        'oddWeek' => 12,
        'evenWeek' => 13,
        'firstWeekOfMonth' => 21,
        'secondWeekOfMonth' => 22,
        'thirdWeekOfMonth' => 23,
        'fourthWeekOfMonth' => 24,
        'lastWeekOfMonth' => 25,
    ];
}
