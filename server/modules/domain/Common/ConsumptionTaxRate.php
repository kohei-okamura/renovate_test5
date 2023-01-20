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
 * 消費税.
 *
 * @method static ConsumptionTaxRate zero() 0%
 * @method static ConsumptionTaxRate eight() 8%
 * @method static ConsumptionTaxRate ten() 10%
 */
final class ConsumptionTaxRate extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'zero' => 0,
        'eight' => 8,
        'ten' => 10,
    ];
}
