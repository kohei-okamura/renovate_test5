<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 事業所区分.
 *
 * @method static Purpose unknown() 不明
 * @method static Purpose internal() 自社
 * @method static Purpose external() 他社
 */
final class Purpose extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'unknown' => 0,
        'internal' => 1,
        'external' => 2,
    ];
}
