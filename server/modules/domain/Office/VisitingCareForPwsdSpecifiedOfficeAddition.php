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
 * 障害福祉サービス：重度訪問介護：特定事業所加算区分.
 *
 * @method static VisitingCareForPwsdSpecifiedOfficeAddition none() なし
 * @method static VisitingCareForPwsdSpecifiedOfficeAddition addition1() 特定事業所加算Ⅰ
 * @method static VisitingCareForPwsdSpecifiedOfficeAddition addition2() 特定事業所加算Ⅱ
 * @method static VisitingCareForPwsdSpecifiedOfficeAddition addition3() 特定事業所加算Ⅲ
 */
final class VisitingCareForPwsdSpecifiedOfficeAddition extends Enum
{
    use VisitingCareForPwsdSpecifiedOfficeAdditionSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
        'addition3' => 3,
    ];
}
