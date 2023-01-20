<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Url;

use Domain\Context\Context;

/**
 * URL生成インターフェース.
 */
interface UrlBuilder
{
    /**
     * URLを生成する.
     *
     * @param \Domain\Context\Context $context
     * @param string $path 先頭に `/` 付ける
     * @return string
     */
    public function build(Context $context, string $path): string;
}
