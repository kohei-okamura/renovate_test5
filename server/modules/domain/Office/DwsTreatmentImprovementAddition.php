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
 * 障害福祉サービス：福祉・介護職員処遇改善加算区分.
 *
 * @method static DwsTreatmentImprovementAddition none() なし
 * @method static DwsTreatmentImprovementAddition addition1() 処遇改善加算（Ⅰ）
 * @method static DwsTreatmentImprovementAddition addition2() 処遇改善加算（Ⅱ）
 * @method static DwsTreatmentImprovementAddition addition3() 処遇改善加算（Ⅲ）
 * @method static DwsTreatmentImprovementAddition addition4() 処遇改善加算（Ⅳ）
 * @method static DwsTreatmentImprovementAddition addition5() 処遇改善加算（Ⅴ）
 * @method static DwsTreatmentImprovementAddition specialAddition() 処遇改善特別加算
 */
final class DwsTreatmentImprovementAddition extends Enum
{
    use DwsTreatmentImprovementAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
        'addition3' => 3,
        'addition4' => 4,
        'addition5' => 5,
        'specialAddition' => 9,
    ];
}
