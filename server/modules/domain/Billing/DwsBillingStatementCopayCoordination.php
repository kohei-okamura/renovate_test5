<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス明細書：上限管理結果.
 *
 * @property-read \Domain\Billing\DwsBillingOffice $office 事業所
 * @property-read \Domain\Billing\CopayCoordinationResult $result 管理結果
 * @property-read int $amount 管理結果額
 */
final class DwsBillingStatementCopayCoordination extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'office',
            'result',
            'amount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'office' => true,
            'result' => true,
            'amount' => true,
        ];
    }
}
