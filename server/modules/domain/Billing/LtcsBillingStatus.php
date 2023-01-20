<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Enum;

/**
 * 介護保険サービス：請求：状態.
 *
 * @method static LtcsBillingStatus checking() 入力中
 * @method static LtcsBillingStatus ready() 未確定
 * @method static LtcsBillingStatus fixed() 確定済
 * @method static LtcsBillingStatus disabled() 無効
 */
final class LtcsBillingStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'checking' => 10,
        'ready' => 20,
        'fixed' => 30,
        'disabled' => 99,
    ];
}
