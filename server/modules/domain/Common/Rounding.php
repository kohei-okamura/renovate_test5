<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 端数処理区分.
 *
 * @method static Rounding none() 未設定
 * @method static Rounding floor() 切り捨て
 * @method static Rounding ceil() 切り上げ
 * @method static Rounding round() 四捨五入
 */
final class Rounding extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'floor' => 1,
        'ceil' => 2,
        'round' => 3,
    ];
}
