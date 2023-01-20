<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 利用者負担上限額管理結果票：費用.
 *
 * @property-read int $fee 総費用額
 * @property-read int $copay 利用者負担額
 * @property-read int $coordinatedCopay 管理結果後利用者負担額
 */
final class DwsBillingCopayCoordinationPayment extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'fee',
            'copay',
            'coordinatedCopay',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'fee' => true,
            'copay' => true,
            'coordinatedCopay' => true,
        ];
    }
}
