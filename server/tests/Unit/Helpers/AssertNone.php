<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use ScalikePHP\Option;

/**
 * 与えられた値が None であることを検証する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertNone
{
    /**
     * 与えられた値が None であることを検証する.
     *
     * @param \ScalikePHP\Option $option
     * @return void
     */
    protected function assertNone(Option $option): void
    {
        $this->assertTrue($option->isEmpty());
    }
}
