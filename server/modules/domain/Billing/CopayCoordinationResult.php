<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * 上限管理結果.
 *
 * @method static CopayCoordinationResult appropriated() 1. 管理事業所で利用者負担額を充当したため、他事業所の利用者負担は発生しない。
 * @method static CopayCoordinationResult notCoordinated() 2. 利用者負担額の合計額が、負担上限月額以下のため、調整事務は行わない。
 * @method static CopayCoordinationResult coordinated() 3. 利用者負担額の合計額が、負担上限月額を超過するため、下記のとおり調整した。
 */
final class CopayCoordinationResult extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'appropriated' => 1,
        'notCoordinated' => 2,
        'coordinated' => 3,
    ];
}
