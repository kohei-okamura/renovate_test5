<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Enum;

/**
 * 請求先.
 *
 * @method static BillingDestination none() 未設定
 * @method static BillingDestination theirself() 本人
 * @method static BillingDestination agent() 本人以外（個人）
 * @method static BillingDestination corporation() 本人以外（法人・団体）
 */
final class BillingDestination extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'theirself' => 1,
        'agent' => 2,
        'corporation' => 3,
    ];
}
