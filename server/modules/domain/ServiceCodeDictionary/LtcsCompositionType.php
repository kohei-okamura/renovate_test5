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
 * 介護保険サービス：合成識別区分.
 *
 * @method static LtcsCompositionType basic() 基本サービスコード
 * @method static LtcsCompositionType composed() 合成サービスコード
 * @method static LtcsCompositionType independent() 単独加減算サービスコード
 */
final class LtcsCompositionType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'basic' => 1,
        'composed' => 2,
        'independent' => 3,
    ];
}
