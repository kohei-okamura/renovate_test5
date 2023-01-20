<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * 位置情報.
 *
 * @property-read null|float $lat
 * @property-read null|float $lng
 */
final class Location extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'lat',
            'lng',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'lat' => true,
            'lng' => true,
        ];
    }
}
