<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 税率区分.
 *
 * @method static TaxCategory unapplicable() 該当なし
 * @method static TaxCategory consumptionTax() 消費税
 * @method static TaxCategory reducedConsumptionTax() 消費税（軽減税率）
 */
final class TaxCategory extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'unapplicable' => 0,
        'consumptionTax' => 1,
        'reducedConsumptionTax' => 2,
    ];
}
