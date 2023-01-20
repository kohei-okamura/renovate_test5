<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\StreamFilter;

/**
 * ストリームフィルター関連ユーリティティ.
 */
final class Utils
{
    /**
     * 入力リソースからバケットを取得する.
     *
     * @param resource $in
     * @return iterable|object[]
     */
    public static function readBuckets($in): iterable
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            yield $bucket;
        }
    }
}
