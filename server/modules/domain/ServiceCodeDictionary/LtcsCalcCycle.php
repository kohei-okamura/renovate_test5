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
 * 介護保険サービス：算定単位.
 *
 * @method static LtcsCalcCycle perService() 1回につき
 * @method static LtcsCalcCycle perDay() 1日につき
 * @method static LtcsCalcCycle perMonth() 1月につき
 */
final class LtcsCalcCycle extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'perService' => 1,
        'perDay' => 2,
        'perMonth' => 3,
    ];
}
