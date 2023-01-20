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
 * 障害福祉サービス：居宅介護：特定事業所加算区分.
 *
 * @method static HomeHelpServiceSpecifiedOfficeAddition none() なし
 * @method static HomeHelpServiceSpecifiedOfficeAddition addition1() 特定事業所加算Ⅰ
 * @method static HomeHelpServiceSpecifiedOfficeAddition addition2() 特定事業所加算Ⅱ
 * @method static HomeHelpServiceSpecifiedOfficeAddition addition3() 特定事業所加算Ⅲ
 * @method static HomeHelpServiceSpecifiedOfficeAddition addition4() 特定事業所加算Ⅳ
 */
final class HomeHelpServiceSpecifiedOfficeAddition extends Enum
{
    use HomeHelpServiceSpecifiedOfficeAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
        'addition3' => 3,
        'addition4' => 4,
    ];
}
