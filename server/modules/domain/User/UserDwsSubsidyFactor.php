<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Enum;

/**
 * 利用者：自治体助成情報：基準値種別.
 *
 * @method static UserDwsSubsidyFactor none() 未設定
 * @method static UserDwsSubsidyFactor fee() 総費用額
 * @method static UserDwsSubsidyFactor copay() 決定利用者負担額
 */
final class UserDwsSubsidyFactor extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'fee' => 1,
        'copay' => 2,
    ];
}
