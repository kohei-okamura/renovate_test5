<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Enum;

/**
 * 時間帯.
 *
 * @method static Timeframe daytime() 日中
 * @method static Timeframe morning() 早朝
 * @method static Timeframe night() 夜間
 * @method static Timeframe midnight() 深夜
 * @method static Timeframe unknown() 未定義
 */
final class Timeframe extends Enum
{
    use TimeframeSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'daytime' => 1,
        'morning' => 2,
        'night' => 3,
        'midnight' => 4,
        'unknown' => 9,
    ];
}
