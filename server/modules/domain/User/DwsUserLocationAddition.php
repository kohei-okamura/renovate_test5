<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Enum;

/**
 * 障害福祉サービス：利用者別地域加算区分.
 *
 * @method static DwsUserLocationAddition none() なし
 * @method static DwsUserLocationAddition specifiedArea() 特別地域加算
 */
final class DwsUserLocationAddition extends Enum
{
    use DwsUserLocationAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'specifiedArea' => 1,
    ];
}
