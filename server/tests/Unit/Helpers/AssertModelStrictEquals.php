<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Domain\ModelCompat;
use Tests\Unit\Constraints\ModelStrictEquals;

/**
 * 2つのモデルオブジェクトが厳密に一致するかどうかを検査する（同一インスタンスかを問わない）.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertModelStrictEquals
{
    /**
     * 2つのモデルオブジェクトが厳密に一致するかどうかを検査する（同一インスタンスかを問わない）.
     *
     * @param \Domain\ModelCompat $expected
     * @param \Domain\ModelCompat $actual
     * @param string $message
     * @return void
     */
    protected function assertModelStrictEquals(ModelCompat $expected, ModelCompat $actual, string $message = ''): void
    {
        $this->assertThat($actual, new ModelStrictEquals($expected), $message);
    }
}
