<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Enum;

/**
 * 利用者請求：請求結果.
 *
 * @method static UserBillingResult pending() 未処理
 * @method static UserBillingResult inProgress() 処理中
 * @method static UserBillingResult paid() 入金済
 * @method static UserBillingResult unpaid() 口座振替未済
 * @method static UserBillingResult none() 請求なし
 */
final class UserBillingResult extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'pending' => 0,
        'inProgress' => 1,
        'paid' => 2,
        'unpaid' => 3,
        'none' => 4,
    ];
}
