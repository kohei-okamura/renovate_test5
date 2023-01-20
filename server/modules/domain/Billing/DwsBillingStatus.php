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
 * 障害福祉サービス：請求：状態.
 *
 * @method static DwsBillingStatus checking() 入力中
 * @method static DwsBillingStatus ready() 未確定
 * @method static DwsBillingStatus fixed() 確定済
 * @method static DwsBillingStatus disabled() 無効
 */
final class DwsBillingStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'checking' => 10,
        'ready' => 20,
        'fixed' => 30,
        'disabled' => 99,
    ];
}
