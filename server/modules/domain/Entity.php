<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

/**
 * Entity base class.
 *
 * @property-read null|int $id ID
 */
abstract class Entity extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return ['id'];
    }
}
