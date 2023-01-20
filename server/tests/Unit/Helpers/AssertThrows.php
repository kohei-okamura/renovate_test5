<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\AssertionFailedError;
use Throwable;

/**
 * 指定した例外が投げられることを検査する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertThrows
{
    /**
     * 指定した例外が投げられることを検査する.
     *
     * @param string $exception
     * @param callable $f
     * @return void
     */
    protected function assertThrows(string $exception, callable $f): void
    {
        try {
            $f();
            $this->fail("must throw {$exception}");
        } catch (AssertionFailedError $error) {
            throw $error;
        } catch (Throwable $throws) {
            $this->assertInstanceOf($exception, $throws, $throws->getMessage());
        }
    }
}
