<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 性別（ISO 5218）.
 *
 * @method static Sex notKnown() 不明
 * @method static Sex male() 男性
 * @method static Sex female() 女性
 * @method static Sex notApplicable() 適用不能
 */
final class Sex extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'notKnown' => 0,
        'male' => 1,
        'female' => 2,
        'notApplicable' => 9,
    ];
}
