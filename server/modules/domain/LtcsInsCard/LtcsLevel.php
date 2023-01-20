<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Domain\Enum;

/**
 * 要介護度（要介護状態区分等）.
 *
 * @method static LtcsLevel target() 事業対象者
 * @method static LtcsLevel supportLevel1() 要支援1
 * @method static LtcsLevel supportLevel2() 要支援2
 * @method static LtcsLevel careLevel1() 要介護1
 * @method static LtcsLevel careLevel2() 要介護2
 * @method static LtcsLevel careLevel3() 要介護3
 * @method static LtcsLevel careLevel4() 要介護4
 * @method static LtcsLevel careLevel5() 要介護5
 */
final class LtcsLevel extends Enum
{
    use LtcsLevelSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'target' => 6,
        'supportLevel1' => 12,
        'supportLevel2' => 13,
        'careLevel1' => 21,
        'careLevel2' => 22,
        'careLevel3' => 23,
        'careLevel4' => 24,
        'careLevel5' => 25,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        6 => '事業対象者',
        12 => '要支援1',
        13 => '要支援2',
        21 => '要介護1',
        22 => '要介護2',
        23 => '要介護3',
        24 => '要介護4',
        25 => '要介護5',
    ];

    /**
     * Resolve LtcsLevel to label.
     *
     * @param \Domain\LtcsInsCard\LtcsLevel $x
     * @return string
     */
    public static function resolve(LtcsLevel $x): string
    {
        return self::$map[$x->value()];
    }
}
