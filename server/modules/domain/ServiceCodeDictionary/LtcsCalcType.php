<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Enum;

/**
 * 介護保険サービス：単位数区分.
 *
 * @method static LtcsCalcType score() 単位数
 * @method static LtcsCalcType baseScore() きざみ基準単位数
 * @method static LtcsCalcType percent() %値
 * @method static LtcsCalcType permille() 1/1000値
 */
final class LtcsCalcType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'score' => 11,
        'baseScore' => 12,
        'percent' => 21,
        'permille' => 22,
    ];
}
