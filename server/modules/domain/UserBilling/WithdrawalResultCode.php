<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Enum;

/**
 * 利用者請求：振替結果コード.
 *
 * @method static WithdrawalResultCode done() 振替済
 * @method static WithdrawalResultCode shortage() 資金不足
 * @method static WithdrawalResultCode noAccount() 取引なし
 * @method static WithdrawalResultCode depositorCause() 預金者都合
 * @method static WithdrawalResultCode noRequest() 依頼書なし
 * @method static WithdrawalResultCode bankingClientCause() 委託者都合
 * @method static WithdrawalResultCode other() その他
 * @method static WithdrawalResultCode pending() 未処理
 */
final class WithdrawalResultCode extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'done' => 0,
        'shortage' => 1,
        'noAccount' => 2,
        'depositorCause' => 3,
        'noRequest' => 4,
        'bankingClientCause' => 8,
        'other' => 9,
        'pending' => 99,
    ];
}
