<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Enum;

/**
 * 送信タイプ.
 *
 * @method static CallingType mail() メール
 * @method static CallingType sms() SMS
 * @method static CallingType telephoneCall() 電話呼び出し
 * @method static CallingType telephoneCallAssigner() 管理スタッフ電話呼び出し
 */
final class CallingType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'mail' => 1,
        'sms' => 2,
        'telephoneCall' => 3,
        'telephoneCallAssigner' => 4,
    ];
}
