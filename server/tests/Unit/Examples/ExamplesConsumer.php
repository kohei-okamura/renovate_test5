<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Lib\LazyField;

/**
 * Example Consumer.
 *
 * @property-read \Tests\Unit\Examples\Examples $examples
 */
trait ExamplesConsumer
{
    use LazyField;

    /**
     * Get the Examples instance.
     *
     * @return \Tests\Unit\Examples\Examples
     */
    final protected function examples(): Examples
    {
        return Examples::instance();
    }
}
