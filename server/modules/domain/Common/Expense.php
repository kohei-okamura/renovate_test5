<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * 費用.
 *
 * @property-read int $taxExcluded 税抜
 * @property-read int $taxIncluded 税込
 * @property-read \Domain\Common\TaxType $taxType 課税区分
 * @property-read \Domain\Common\TaxCategory $taxCategory 税率区分
 */
final class Expense extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'taxExcluded',
            'taxIncluded',
            'taxType',
            'taxCategory',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'taxExcluded' => true,
            'taxIncluded' => true,
            'taxType' => true,
            'taxCategory' => true,
        ];
    }
}
