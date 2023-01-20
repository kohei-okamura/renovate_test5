<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

/**
 * カナの変換ユーティリティ.
 */
final class KanaConverter
{
    // やや冗長かもしれないが「アイヌ語仮名」まで含めている（ただし「ㇷ゚」は扱いが難しいため除外）。
    private static array $lowerCases = ['ァ', 'ィ', 'ゥ', 'ェ', 'ォ', 'ッ', 'ャ', 'ュ', 'ョ', 'ヮ', 'ヵ', 'ㇰ', 'ヶ', 'ㇱ', 'ㇲ', 'ㇳ', 'ㇴ', 'ㇵ', 'ㇶ', 'ㇷ', 'ㇸ', 'ㇹ', 'ㇺ', 'ㇻ', 'ㇼ', 'ㇽ', 'ㇾ', 'ㇿ', 'ヮ'];
    private static array $upperCases = ['ア', 'イ', 'ウ', 'エ', 'オ', 'ツ', 'ヤ', 'ユ', 'ヨ', 'ワ', 'カ', 'ク', 'ケ', 'シ', 'ス', 'ト', 'ヌ', 'ハ', 'ヒ', 'フ', 'ヘ', 'ホ', 'ム', 'ラ', 'リ', 'ル', 'レ', 'ロ', 'ワ'];

    /**
     * 小文字のカタカナを大文字のカタカナに変換する.
     *
     * @param string $subject カタカナ文字列
     * @return string 大文字のカタカナ
     */
    public static function lowerCaseToUpperCase(string $subject): string
    {
        return str_replace(self::$lowerCases, self::$upperCases, $subject);
    }

    /**
     * 全角カタカナを大文字の半角カタカナに変換する.
     *
     * @param string $value 全角カタカナ
     * @return string 大文字の半角カタカナ
     */
    public static function toUppercaseHalfWidthKatakana(string $value): string
    {
        return mb_convert_kana(self::lowerCaseToUpperCase($value), 'ask');
    }
}
