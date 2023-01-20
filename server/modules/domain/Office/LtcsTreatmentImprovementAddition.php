<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 介護保険サービス：介護職員処遇改善加算区分.
 *
 * @method static LtcsTreatmentImprovementAddition none() なし
 * @method static LtcsTreatmentImprovementAddition addition1() 処遇改善加算（Ⅰ）
 * @method static LtcsTreatmentImprovementAddition addition2() 処遇改善加算（Ⅱ）
 * @method static LtcsTreatmentImprovementAddition addition3() 処遇改善加算（Ⅲ）
 * @method static LtcsTreatmentImprovementAddition addition4() 処遇改善加算（Ⅳ）
 * @method static LtcsTreatmentImprovementAddition addition5() 処遇改善加算（Ⅴ）
 */
final class LtcsTreatmentImprovementAddition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
        'addition3' => 3,
        'addition4' => 4,
        'addition5' => 5,
    ];
}
