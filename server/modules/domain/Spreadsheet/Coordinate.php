<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Spreadsheet;

use Lib\Exceptions\InvalidArgumentException;
use Lib\Math;
use ScalikePHP\Seq;
use Traversable;

/**
 * スプレッドシートのセル位置に関するユーティリティ.
 */
final class Coordinate
{
    /**
     * セル位置（A1 形式）を取得する.
     *
     * @param int|string $column A1 形式または C1R1 形式の列番号
     * @param int $row 行番号
     * @return string
     */
    public static function cell($column, int $row): string
    {
        return sprintf('%s%d', self::getColumnCoordinate($column), $row);
    }

    /**
     * 列番号（A1 形式）の一覧を取得する.
     *
     * @return \ScalikePHP\Seq
     */
    public static function columns(): Seq
    {
        return Seq::from(1)->flatMap(function (int $x): Traversable {
            for ($i = $x;; ++$i) {
                yield self::computeColumnCoordinate($i);
            }
        });
    }

    /**
     * セル範囲を A1 形式の文字列として取得する.
     *
     * @param string $start 開始セル位置（A1 形式）
     * @param string $end 終了セル位置（A1 形式）
     * @return string
     */
    public static function range(string $start, string $end): string
    {
        return sprintf('%s:%s', $start, $end);
    }

    /**
     * 列番号（C1R1 形式）から列番号（A1 形式）を取得する.
     *
     * @param int $column
     * @return string
     */
    private static function computeColumnCoordinate(int $column): string
    {
        $n = $column - 1;
        return $n < 26 ? chr(65 + $n) : self::computeColumnCoordinate(Math::floor($n / 26)) . chr(65 + $n % 26);
    }

    /**
     * 列番号（A1 形式）を取得する.
     *
     * @param int|string $column A1 形式または C1R1 形式の列番号
     * @return string A1 形式の列番号
     */
    private static function getColumnCoordinate($column): string
    {
        if (is_string($column)) {
            return $column;
        } elseif (is_int($column)) {
            return self::computeColumnCoordinate($column);
        } else {
            $type = gettype($column) === 'object' ? get_class($column) : gettype($column); // @codeCoverageIgnore
            throw new InvalidArgumentException("Column should be a int or string: {$type} given"); // @codeCoverageIgnore
        }
    }
}
