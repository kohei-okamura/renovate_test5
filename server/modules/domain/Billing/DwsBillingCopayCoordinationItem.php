<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 利用者負担上限額管理結果票：明細.
 *
 * @property-read int $itemNumber 項番
 * @property-read \Domain\Billing\DwsBillingOffice $office 事業所
 * @property-read \Domain\Billing\DwsBillingCopayCoordinationPayment $subtotal 利用者負担額集計・調整欄
 */
final class DwsBillingCopayCoordinationItem extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'itemNumber',
            'office',
            'subtotal',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'itemNumber' => true,
            'office' => true,
            'subtotal' => true,
        ];
    }
}
