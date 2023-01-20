<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * 距離.
 *
 * @property-read null|float $distance
 * @property-read \Domain\Model $destination
 */
final class Distance extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'distance',
            'destination',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'distance' => true,
            'destination' => true,
        ];
    }
}
