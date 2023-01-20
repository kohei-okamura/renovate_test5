<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Model;

/**
 * 介護保険サービス：算定単位数.
 *
 * @property-read int $value 単位値
 * @property-read \Domain\ServiceCodeDictionary\LtcsCalcType $calcType 単位値区分
 * @property-read \Domain\ServiceCodeDictionary\LtcsCalcCycle $calcCycle 算定単位
 */
final class LtcsCalcScore extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'value',
            'calcType',
            'calcCycle',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'value' => true,
            'calcType' => true,
            'calcCycle' => true,
        ];
    }
}
