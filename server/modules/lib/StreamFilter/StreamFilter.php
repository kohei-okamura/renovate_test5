<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib\StreamFilter;

/**
 * ストリームフィルター処理クラス.
 */
final class StreamFilter
{
    /**
     * StreamFilterPathBuilder の新しいインスタンスを取得する.
     *
     * @return \Lib\StreamFilter\StreamFilterPathBuilder
     */
    public static function pathBuilder(): StreamFilterPathBuilder
    {
        return StreamFilterPathBuilder::create();
    }

    /**
     * 改行コード変換フィルター文字列を返す.
     *
     * @return string
     */
    public static function crlf(): string
    {
        stream_filter_register('convert.crlf', CrlfFilter::class);
        return 'convert.crlf';
    }

    /**
     * iconv フィルター文字列を返す.
     *
     * @param string $fromEncoding
     * @param string $toEncoding
     * @return string
     */
    public static function iconv(string $fromEncoding, string $toEncoding): string
    {
        return "convert.iconv.{$fromEncoding}.{$toEncoding}";
    }
}
