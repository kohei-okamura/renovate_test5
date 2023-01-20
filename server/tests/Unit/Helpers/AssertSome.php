<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use ScalikePHP\Option;

/**
 * 与えられた値が Some であることを検証し、その値を使って関数を実行する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertSome
{
    /**
     * 与えられた値が Some であることを検証し、その値を使って関数を実行する.
     *
     * @param \ScalikePHP\Option $option
     * @param callable $f
     * @return void
     */
    protected function assertSome(Option $option, callable $f): void
    {
        $this->assertTrue($option->nonEmpty());
        $option->each($f);
    }
}
