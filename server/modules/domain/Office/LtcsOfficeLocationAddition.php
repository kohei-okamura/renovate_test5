<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 介護保険サービス：地域加算区分.
 *
 * @method static LtcsOfficeLocationAddition none() なし
 * @method static LtcsOfficeLocationAddition specifiedArea() 特別地域訪問介護加算
 * @method static LtcsOfficeLocationAddition mountainousArea() 中山間地域等における小規模事業所加算
 */
final class LtcsOfficeLocationAddition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'specifiedArea' => 1,
        'mountainousArea' => 2,
    ];
}
