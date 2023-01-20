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
 * 介護保険サービス：介護職員等特定処遇改善加算区分.
 *
 * @method static LtcsSpecifiedTreatmentImprovementAddition none() なし
 * @method static LtcsSpecifiedTreatmentImprovementAddition addition1() 特定処遇改善加算（Ⅰ）
 * @method static LtcsSpecifiedTreatmentImprovementAddition addition2() 特定処遇改善加算（Ⅱ）
 */
final class LtcsSpecifiedTreatmentImprovementAddition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
    ];
}
