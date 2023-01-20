<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Enum;

/**
 * テスト用の Enum.
 *
 * @method static EnumFixture keyOfString
 * @method static EnumFixture keyOfInt
 */
class EnumFixture extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'keyOfString' => 'stringValue',
        'keyOfInt' => 11,
    ];
}
