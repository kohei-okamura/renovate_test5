<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Modules;

use Codeception\Module;
use Hamcrest\Util;

/**
 * Hamcrest のグローバル関数を利用可能にする.
 */
final class Hamcrest extends Module
{
    /** {@inheritdoc} */
    public function _beforeSuite($settings = []): void
    {
        Util::registerGlobalFunctions();
    }
}
