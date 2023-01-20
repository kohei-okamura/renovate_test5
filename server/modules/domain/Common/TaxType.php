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
 * 課税区分.
 *
 * @method static TaxType taxExcluded() 税抜
 * @method static TaxType taxIncluded() 税込
 * @method static TaxType taxExempted() 非課税
 */
final class TaxType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'taxExcluded' => 1,
        'taxIncluded' => 2,
        'taxExempted' => 3,
    ];
}
