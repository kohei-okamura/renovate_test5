<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Domain\Common\Carbon;

/**
 * Versionable trait for Domain
 *
 * @mixin \Domain\Model
 */
trait Versionable
{
    /** {@inheritdoc} */
    protected function defaults(): array
    {
        $defaults = [
            'version' => 1,
            'isEnabled' => true,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return $defaults + parent::defaults();
    }
}
