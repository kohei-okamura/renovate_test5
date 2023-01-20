<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Context;

/**
 * ContextProvider Interface.
 */
interface ContextProvider
{
    /**
     * コンテキストを返す.
     *
     * @return \Domain\Context\Context
     */
    public function context(): Context;
}
