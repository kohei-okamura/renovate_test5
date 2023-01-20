<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Model;

/**
 * 児童.
 *
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @property-read null|\Domain\Common\Carbon $birthday 生年月日
 */
final class Child extends Model
{
    protected function attrs(): array
    {
        return [
            'name',
            'birthday',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'name' => true,
            'birthday' => true,
        ];
    }
}
