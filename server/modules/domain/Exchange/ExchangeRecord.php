<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Polite;

/**
 * 交換情報レコード.
 */
abstract class ExchangeRecord extends Polite
{
    /** @var string フォーマット：年月日 */
    public const FORMAT_DATE = 'Ymd';

    /** @var string フォーマット：年月 */
    public const FORMAT_YEAR_MONTH = 'Ym';

    /** @var string 未使用 */
    public const UNUSED = '';

    /** @var int レコード種別：コントロールレコード */
    public const RECORD_TYPE_CONTROL = 1;

    /** @var int レコード種別：データレコード */
    public const RECORD_TYPE_DATA = 2;

    /** @var int レコード種別：エンドレコード */
    public const RECORD_TYPE_END = 3;

    /** @var string 予備 */
    public const RESERVED = '';

    /**
     * CSV の各列を表す配列に変換する.
     *
     * @param int $recordNumber レコード番号（連番）
     * @return array
     */
    abstract public function toArray(int $recordNumber): array;

    /**
     * 真偽値をフォーマットする.
     *
     * @param null|bool|int $value
     * @param string $trueValue
     * @param string $falseValue
     * @return string
     */
    protected static function formatBoolean(
        null|bool|int $value,
        string $trueValue = '1',
        string $falseValue = ''
    ): string {
        return $value ? $trueValue : $falseValue;
    }

    /**
     * 日付をフォーマットする.
     *
     * @param null|\Domain\Common\Carbon $value
     * @return string
     */
    protected static function formatDate(?Carbon $value): string
    {
        return $value === null ? '' : $value->format(self::FORMAT_DATE);
    }

    /**
     * 時刻文字列をフォーマットする.
     *
     * @param null|string $value
     * @return string
     */
    protected static function formatTimeString(?string $value): string
    {
        return preg_replace('/\A(\d{2}):?(\d{2})\z/', '$1$2', $value);
    }

    /**
     * 年月をフォーマットする.
     *
     * @param null|\Domain\Common\Carbon $value
     * @return string
     */
    protected static function formatYearMonth(?Carbon $value): string
    {
        return $value === null ? '' : $value->format(self::FORMAT_YEAR_MONTH);
    }
}
