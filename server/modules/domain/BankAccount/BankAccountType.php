<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\BankAccount;

use Domain\Enum;

/**
 * 銀行口座：種別.
 *
 * @method static BankAccountType unknown() 不明
 * @method static BankAccountType ordinaryDeposit() 普通預金
 * @method static BankAccountType currentDeposit() 当座預金
 * @method static BankAccountType fixedDeposit() 定期預金
 */
final class BankAccountType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'unknown' => 0,
        'ordinaryDeposit' => 1,
        'currentDeposit' => 2,
        'fixedDeposit' => 3,
    ];
}
