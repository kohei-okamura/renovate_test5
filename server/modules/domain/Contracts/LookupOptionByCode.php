<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Contracts;

use ScalikePHP\Option;

/**
 * コードでの取得を可能とする.
 */
interface LookupOptionByCode
{
    /**
     * Lookup an entity by code from repository.
     *
     * @param string $code
     * @return \Domain\Entity[]|\ScalikePHP\Option
     */
    public function lookupOptionByCode(string $code): Option;
}
