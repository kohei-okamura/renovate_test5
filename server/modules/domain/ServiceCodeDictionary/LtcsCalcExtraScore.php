<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Model;

/**
 * 介護保険サービス：きざみ単位数.
 *
 * @property-read bool $isAvailable きざみ有無
 * @property-read int $baseMinutes きざみ基準時間数
 * @property-read int $unitScore きざみ単位数
 * @property-read int $unitMinutes きざみ時間量
 * @property-read int $specifiedOfficeAdditionCoefficient 特定事業所加算係数
 * @property-read int $timeframeAdditionCoefficient 時間帯係数
 */
final class LtcsCalcExtraScore extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'isAvailable',
            'baseMinutes',
            'unitScore',
            'unitMinutes',
            'specifiedOfficeAdditionCoefficient',
            'timeframeAdditionCoefficient',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'isAvailable' => true,
            'baseMinutes' => true,
            'unitScore' => true,
            'unitMinutes' => true,
            'specifiedOfficeAdditionCoefficient' => true,
            'timeframeAdditionCoefficient' => true,
        ];
    }
}
