<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Enum;

/**
 * スタッフ：状態.
 *
 * @method static StaffStatus provisional() 仮登録
 * @method static StaffStatus active() 在職中
 * @method static StaffStatus retired() 退職
 */
final class StaffStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'provisional' => 1,
        'active' => 2,
        'retired' => 9,
    ];
}
