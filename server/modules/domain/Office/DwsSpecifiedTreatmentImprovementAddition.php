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
 * 障害福祉サービス：福祉・介護職員等特定処遇改善加算区分.
 *
 * @method static DwsSpecifiedTreatmentImprovementAddition none() なし
 * @method static DwsSpecifiedTreatmentImprovementAddition addition1() 特定処遇改善加算（Ⅰ）
 * @method static DwsSpecifiedTreatmentImprovementAddition addition2() 特定処遇改善加算（Ⅱ）
 */
final class DwsSpecifiedTreatmentImprovementAddition extends Enum
{
    use DwsSpecifiedTreatmentImprovementAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
    ];
}
