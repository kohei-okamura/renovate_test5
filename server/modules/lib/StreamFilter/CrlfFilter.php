<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\StreamFilter;

use php_user_filter;

/**
 * 改行コードフィルター.
 *
 * 改行を CRLF に統一する.
 */
final class CrlfFilter extends php_user_filter
{
    /** {@inheritdoc} */
    public function filter($in, $out, &$consumed, $closing): int
    {
        foreach (Utils::readBuckets($in) as $bucket) {
            $bucket->data = preg_replace('/\r?\n$/m', "\r\n", $bucket->data);
            stream_bucket_append($out, $bucket);
        }
        return \PSFS_PASS_ON;
    }
}
