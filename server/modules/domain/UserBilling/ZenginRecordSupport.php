<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Lib\Exceptions\InvalidArgumentException;

/**
 * 全銀レコード内部ユーティリティ.
 */
trait ZenginRecordSupport
{
    /**
     * レコード文字列を属性値の連想配列に変換する.
     *
     * @param string $recordString
     * @param array $format
     * @return array|string[]
     */
    public static function parseRecordString(string $recordString, array $format): array
    {
        $offset = 0;
        $result = [];
        foreach ($format as $key => $length) {
            $result[$key] = str_replace(' ', '', mb_substr($recordString, $offset, $length));
            $offset += $length;
        }
        return $result;
    }

    /**
     * 数値文字列であることを保証する.
     *
     * @param string $numeric
     * @throws \Lib\Exceptions\InvalidArgumentException
     */
    private static function ensureNumeric(string $numeric): void
    {
        if (!is_numeric($numeric)) {
            throw new InvalidArgumentException("{$numeric} is not numeric");
        }
    }

    /**
     * 文字列を固定長の他の文字列で埋める.
     *
     * @param mixed $input
     * @param int $length
     * @param string $char
     * @param int $padStyle
     * @return string
     */
    private static function pad($input, int $length, string $char, int $padStyle): string
    {
        $inputString = (string)$input;
        $mbPadLength = strlen($inputString) - mb_strlen($inputString, 'utf-8') + $length;
        return str_pad($inputString, $mbPadLength, $char, $padStyle);
    }

    /**
     * 数値文字列であることを保証する.
     *
     * @param string $numeric
     * @throws \Lib\Exceptions\InvalidArgumentException
     * @return int
     */
    private static function parseNumeric(string $numeric): int
    {
        if (!is_numeric($numeric)) {
            throw new InvalidArgumentException("{$numeric} is not numeric");
        }
        return (int)$numeric;
    }
}
