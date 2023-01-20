<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * 位置情報取得ゲートウェイ.
 */
interface LocationResolver
{
    /**
     * Google Geocoding API を利用して位置情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Addr $addr
     * @return \Domain\Common\Location[]|\ScalikePHP\Option
     */
    public function resolve(Context $context, Addr $addr): Option;
}
