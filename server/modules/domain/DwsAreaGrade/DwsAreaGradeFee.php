<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsAreaGrade;

use Domain\Entity;

/**
 * 障害福祉サービス：地域区分単価.
 *
 * @property-read int $id
 * @property-read int $dwsAreaGradeId 地域区分 ID
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用日
 * @property-read \Domain\Common\Decimal $fee 単価
 */
final class DwsAreaGradeFee extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsAreaGradeId',
            'effectivatedOn',
            'fee',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'dwsAreaGradeId' => true,
            'effectivatedOn' => true,
            'fee' => true,
        ];
    }
}
