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
 * 利用者：自治体助成情報：給付方式.
 *
 * @method static UserDwsSubsidyType benefitRate() 定率給付
 * @method static UserDwsSubsidyType copayRate() 定率負担
 * @method static UserDwsSubsidyType benefitAmount() 定額給付
 * @method static UserDwsSubsidyType copayAmount() 定額負担
 */
final class UserDwsSubsidyType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'benefitRate' => 1,
        'copayRate' => 4,
        'benefitAmount' => 2,
        'copayAmount' => 3,
    ];
}
