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
 * 連絡先電話番号：続柄・関係.
 *
 * @method static ContactRelationship theirself() 本人
 * @method static ContactRelationship family() 家族
 * @method static ContactRelationship lawyer() 弁護士
 * @method static ContactRelationship others() その他
 */
final class ContactRelationship extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'theirself' => 10,
        'family' => 20,
        'lawyer' => 30,
        'others' => 99,
    ];
}
