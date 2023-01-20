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
 * 支払方法.
 *
 * @method static PaymentMethod none() 未設定
 * @method static PaymentMethod withdrawal() 口座振替
 * @method static PaymentMethod transfer() 銀行振込
 * @method static PaymentMethod collection() 集金
 */
final class PaymentMethod extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'withdrawal' => 1,
        'transfer' => 2,
        'collection' => 3,
    ];
}
