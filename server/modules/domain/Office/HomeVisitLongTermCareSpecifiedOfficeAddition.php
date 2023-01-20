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
 * 介護保険サービス：訪問介護：特定事業所加算区分.
 *
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition none() なし
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition1() 特定事業所加算Ⅰ
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition2() 特定事業所加算Ⅱ
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition3() 特定事業所加算Ⅲ
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition4() 特定事業所加算Ⅳ
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition5() 特定事業所加算Ⅴ
 * @method static HomeVisitLongTermCareSpecifiedOfficeAddition addition35() 特定事業所加算Ⅲ＋Ⅴ
 */
final class HomeVisitLongTermCareSpecifiedOfficeAddition extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'addition1' => 1,
        'addition2' => 2,
        'addition3' => 3,
        'addition4' => 4,
        'addition5' => 5,
        'addition35' => 35,
    ];
}
