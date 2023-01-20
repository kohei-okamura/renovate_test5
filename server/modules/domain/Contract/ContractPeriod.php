<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Contract;

use Domain\Model;

/**
 * 契約：提供期間.
 *
 * @property-read null|\Domain\Common\Carbon $start 初回サービス提供日
 * @property-read null|\Domain\Common\Carbon $end 最終サービス提供日
 */
final class ContractPeriod extends Model
{
    /** {@inheritdoc} */
    public function attrs(): array
    {
        return [
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'start' => 'date',
            'end' => 'date',
        ];
    }
}
