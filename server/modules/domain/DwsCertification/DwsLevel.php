<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Enum;

/**
 * 障害支援区分.
 *
 * @method static DwsLevel notApplicable() 非該当
 * @method static DwsLevel level1() 区分1
 * @method static DwsLevel level2() 区分2
 * @method static DwsLevel level3() 区分3
 * @method static DwsLevel level4() 区分4
 * @method static DwsLevel level5() 区分5
 * @method static DwsLevel level6() 区分6
 */
final class DwsLevel extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'notApplicable' => 99,
        'level1' => 21,
        'level2' => 22,
        'level3' => 23,
        'level4' => 24,
        'level5' => 25,
        'level6' => 26,
    ];
}
