<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Enum;

/**
 * 都道府県.
 *
 * @method static Prefecture none() 未設定
 * @method static Prefecture hokkaido() 北海道
 * @method static Prefecture aomori() 青森県
 * @method static Prefecture iwate() 岩手県
 * @method static Prefecture miyagi() 宮城県
 * @method static Prefecture akita() 秋田県
 * @method static Prefecture yamagata() 山形県
 * @method static Prefecture fukushima() 福島県
 * @method static Prefecture ibaraki() 茨城県
 * @method static Prefecture tochigi() 栃木県
 * @method static Prefecture gunma() 群馬県
 * @method static Prefecture saitama() 埼玉県
 * @method static Prefecture chiba() 千葉県
 * @method static Prefecture tokyo() 東京都
 * @method static Prefecture kanagawa() 神奈川県
 * @method static Prefecture niigata() 新潟県
 * @method static Prefecture toyama() 富山県
 * @method static Prefecture ishikawa() 石川県
 * @method static Prefecture fukui() 福井県
 * @method static Prefecture yamanashi() 山梨県
 * @method static Prefecture nagano() 長野県
 * @method static Prefecture gifu() 岐阜県
 * @method static Prefecture shizuoka() 静岡県
 * @method static Prefecture aichi() 愛知県
 * @method static Prefecture mie() 三重県
 * @method static Prefecture shiga() 滋賀県
 * @method static Prefecture kyoto() 京都府
 * @method static Prefecture osaka() 大阪府
 * @method static Prefecture hyogo() 兵庫県
 * @method static Prefecture nara() 奈良県
 * @method static Prefecture wakayama() 和歌山県
 * @method static Prefecture tottori() 鳥取県
 * @method static Prefecture shimane() 島根県
 * @method static Prefecture okayama() 岡山県
 * @method static Prefecture hiroshima() 広島県
 * @method static Prefecture yamaguchi() 山口県
 * @method static Prefecture tokushima() 徳島県
 * @method static Prefecture kagawa() 香川県
 * @method static Prefecture ehime() 愛媛県
 * @method static Prefecture kochi() 高知県
 * @method static Prefecture fukuoka() 福岡県
 * @method static Prefecture saga() 佐賀県
 * @method static Prefecture nagasaki() 長崎県
 * @method static Prefecture kumamoto() 熊本県
 * @method static Prefecture oita() 大分県
 * @method static Prefecture miyazaki() 宮崎県
 * @method static Prefecture kagoshima() 鹿児島県
 * @method static Prefecture okinawa() 沖縄県
 */
final class Prefecture extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'hokkaido' => 1,
        'aomori' => 2,
        'iwate' => 3,
        'miyagi' => 4,
        'akita' => 5,
        'yamagata' => 6,
        'fukushima' => 7,
        'ibaraki' => 8,
        'tochigi' => 9,
        'gunma' => 10,
        'saitama' => 11,
        'chiba' => 12,
        'tokyo' => 13,
        'kanagawa' => 14,
        'niigata' => 15,
        'toyama' => 16,
        'ishikawa' => 17,
        'fukui' => 18,
        'yamanashi' => 19,
        'nagano' => 20,
        'gifu' => 21,
        'shizuoka' => 22,
        'aichi' => 23,
        'mie' => 24,
        'shiga' => 25,
        'kyoto' => 26,
        'osaka' => 27,
        'hyogo' => 28,
        'nara' => 29,
        'wakayama' => 30,
        'tottori' => 31,
        'shimane' => 32,
        'okayama' => 33,
        'hiroshima' => 34,
        'yamaguchi' => 35,
        'tokushima' => 36,
        'kagawa' => 37,
        'ehime' => 38,
        'kochi' => 39,
        'fukuoka' => 40,
        'saga' => 41,
        'nagasaki' => 42,
        'kumamoto' => 43,
        'oita' => 44,
        'miyazaki' => 45,
        'kagoshima' => 46,
        'okinawa' => 47,
    ];

    /**
     * Map for resolve function.
     */
    private static array $map = [
        0 => '未設定',
        1 => '北海道',
        2 => '青森県',
        3 => '岩手県',
        4 => '宮城県',
        5 => '秋田県',
        6 => '山形県',
        7 => '福島県',
        8 => '茨城県',
        9 => '栃木県',
        10 => '群馬県',
        11 => '埼玉県',
        12 => '千葉県',
        13 => '東京都',
        14 => '神奈川県',
        15 => '新潟県',
        16 => '富山県',
        17 => '石川県',
        18 => '福井県',
        19 => '山梨県',
        20 => '長野県',
        21 => '岐阜県',
        22 => '静岡県',
        23 => '愛知県',
        24 => '三重県',
        25 => '滋賀県',
        26 => '京都府',
        27 => '大阪府',
        28 => '兵庫県',
        29 => '奈良県',
        30 => '和歌山県',
        31 => '鳥取県',
        32 => '島根県',
        33 => '岡山県',
        34 => '広島県',
        35 => '山口県',
        36 => '徳島県',
        37 => '香川県',
        38 => '愛媛県',
        39 => '高知県',
        40 => '福岡県',
        41 => '佐賀県',
        42 => '長崎県',
        43 => '熊本県',
        44 => '大分県',
        45 => '宮崎県',
        46 => '鹿児島県',
        47 => '沖縄県',
    ];

    /**
     * Resolve Prefecture to label.
     *
     * @param \Domain\Common\Prefecture $x
     * @return string
     */
    public static function resolve(Prefecture $x): string
    {
        return self::$map[$x->value()];
    }
}
