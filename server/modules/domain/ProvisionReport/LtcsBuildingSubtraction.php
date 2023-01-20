<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Enum;

/**
 * 介護保険サービス：同一建物減算区分.
 *
 * @method static LtcsBuildingSubtraction none() なし
 * @method static LtcsBuildingSubtraction subtraction1() 同一建物減算1
 * @method static LtcsBuildingSubtraction subtraction2() 同一建物減算2
 */
final class LtcsBuildingSubtraction extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'subtraction1' => 1,
        'subtraction2' => 2,
    ];
}
