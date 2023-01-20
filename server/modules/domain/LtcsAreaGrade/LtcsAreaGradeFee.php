<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsAreaGrade;

use Domain\Entity;

/**
 * 介護保険サービス：地域区分単価.
 *
 * @property-read int $id
 * @property-read int $ltcsAreaGradeId 地域区分 ID
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用日
 * @property-read \Domain\Common\Decimal $fee 単価
 */
final class LtcsAreaGradeFee extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'ltcsAreaGradeId',
            'effectivatedOn',
            'fee',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'ltcsAreaGradeId' => true,
            'effectivatedOn' => true,
            'fee' => true,
        ];
    }
}
