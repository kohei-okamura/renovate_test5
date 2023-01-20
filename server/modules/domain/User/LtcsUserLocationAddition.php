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
 * 介護保険サービス：利用者別地域加算区分.
 *
 * @method static LtcsUserLocationAddition none() なし
 * @method static LtcsUserLocationAddition mountainousArea() 中山間地域等に居住する者へのサービス提供加算
 */
final class LtcsUserLocationAddition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'mountainousArea' => 1,
    ];
}
