<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Arr;

/**
 * Fake Office Name Provider
 */
final class FakeOfficeNameProvider extends Base
{
    private const OFFICE_NAMES = [
        ['土屋訪問介護事業所 札幌', '札幌', 'ツチヤホウモンカイゴジギョウショサッポロ'],
        ['土屋訪問介護事業所 仙台', '仙台', 'ツチヤホウモンカイゴジギョウショセンダイ'],
        ['土屋訪問介護事業所 山形', '山形', 'ツチヤホウモンカイゴジギョウショヤマガタ'],
        ['土屋訪問介護事業所 福島', '福島', 'ツチヤホウモンカイゴジギョウショフクシマ'],
        ['土屋訪問介護事業所 宇都宮', '宇都宮', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 さいたま', 'さいたま', 'ツチヤホウモンカイゴジギョウショサイタマ'],
        ['土屋訪問介護事業所 船橋', '船橋', 'ツチヤホウモンカイゴジギョウショフナバシ'],
        ['土屋訪問介護事業所', '土屋訪問介護事業所', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 横浜', '横浜', 'ツチヤホウモンカイゴジギョウショヨコハマ'],
        ['土屋訪問介護事業所 新潟', '新潟', 'ツチヤホウモンカイゴジギョウショニイガタ'],
        ['土屋訪問介護事業所 静岡', '静岡', 'ツチヤホウモンカイゴジギョウショシズオカ'],
        ['土屋訪問介護事業所 名古屋', '名古屋', 'ツチヤホウモンカイゴジギョウショナゴヤ'],
        ['土屋訪問介護事業所 京都', '京都', 'ツチヤホウモンカイゴジギョウショキョウト'],
        ['土屋訪問介護事業所 大阪', '大阪', 'ツチヤホウモンカイゴジギョウショオオサカ'],
        ['土屋訪問介護事業所 尼崎', '尼崎', 'ツチヤホウモンカイゴジギョウショアマガサキ'],
        ['土屋訪問介護事業所 岡山', '岡山', 'ツチヤホウモンカイゴジギョウショオカヤマ'],
        ['土屋訪問介護事業所 広島', '広島', 'ツチヤホウモンカイゴジギョウショヒロシマ'],
        ['土屋訪問介護事業所 高松', '高松', 'ツチヤホウモンカイゴジギョウショタカマツ'],
        ['土屋訪問介護事業所 福岡', '福岡', 'ツチヤホウモンカイゴジギョウショフクオカ'],
        ['土屋訪問介護事業所 新宿', '新宿', 'ツチヤホウモンカイゴジギョウショシンジュク'],
        ['土屋訪問介護事業所 世田谷', '世田谷', 'ツチヤホウモンカイゴジギョウショセタガヤ'],
        ['土屋訪問介護事業所 中野北', '中野北', 'ツチヤホウモンカイゴジギョウショナカノキタ'],
        ['土屋訪問介護事業所 下井草', '下井草', 'ツチヤホウモンカイゴジギョウショシモイグサ'],
        ['土屋訪問介護事業所 豊島', '豊島', 'ツチヤホウモンカイゴジギョウショトシマ'],
        ['土屋訪問介護事業所 北', '北', 'ツチヤホウモンカイゴジギョウショキタ'],
        ['土屋訪問介護事業所 板橋', '板橋', 'ツチヤホウモンカイゴジギョウショイタバシ'],
        ['土屋訪問介護事業所 練馬', '練馬', 'ツチヤホウモンカイゴジギョウショネリマ'],
        ['土屋訪問介護事業所 倉敷', '倉敷', 'ツチヤホウモンカイゴジギョウショクラシキ'],
        ['土屋訪問介護事業所 福山', '福山', 'ツチヤホウモンカイゴジギョウショフクヤマ'],
        ['ユースタイル 諏訪の森', '諏訪の森', 'ユースタイルスワノモリ'],
        ['デイサービス土屋 中野坂上', 'デイ中野坂上', 'デイサービスツチヤナカノサカウエ'],
        ['デイサービス土屋 中野中央', 'デイ中野中央', 'デイサービスツチヤナカノチュウオウ'],
        ['デイサービス土屋 若宮', 'デイ若宮', 'デイサービスツチヤワカミヤ'],
        ['デイサービス土屋 都立家政', 'デイ都立家政', 'デイサービスツチヤトリツカセイ'],
        ['土屋訪問看護ステーション', '土屋訪問看護ステーション', 'ツチヤホウモンカンゴステーション'],
        ['ユースタイルカレッジ', 'カレッジ', 'ユースタイルカレッジ'],
        ['土屋訪問マッサージ 中野店', 'マッサージ中野店', 'ツチヤホウモンマッサージ'],
        ['居宅介護支援 土屋ケア', 'ケア', 'キョタクカイゴシエンツチヤケア'],
        ['ユースタイルラボラトリー', '本社', 'ユースタイルラボラトリー'],
        ['土屋訪問介護事業所 千葉', '千葉', 'ツチヤホウモンカイゴジギョウショチバ'],
        ['土屋訪問介護事業所 足立', '足立', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 立川', '立川', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 甲府', '甲府', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 岐阜', '岐阜', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 多治見', '多治見', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 浜松', '浜松', 'ツチヤホウモンカイゴジギョウショ'],
        ['土屋訪問介護事業所 豊橋', '豊橋', 'ツチヤホウモンカイゴジギョウショ'],
        ['居宅介護支援 土屋ケア 中野北', 'ケア中野北', 'キョタクカイゴシエンツチヤケアナカノキタ'],
        ['土屋訪問介護事業所 四日市', '四日市', 'ツチヤホウモンカイゴジギョウショヨッカイチ'],
        ['土屋訪問介護事業所 大津', '大津', 'ツチヤホウモンカイゴジギョウショオオツ'],
        ['土屋訪問介護事業所 岸和田', '岸和田', 'ツチヤホウモンカイゴジギョウショキシワダ'],
        ['土屋訪問介護事業所 枚方', '枚方', 'ツチヤホウモンカイゴジギョウショヒラカタ'],
        ['土屋訪問介護事業所 神戸', '神戸', 'ツチヤホウモンカイゴジギョウショコウベ'],
        ['土屋訪問介護事業所 姫路', '姫路', 'ツチヤホウモンカイゴジギョウショヒメジ'],
        ['土屋訪問介護事業所 奈良', '奈良', 'ツチヤホウモンカイゴジギョウショナラ'],
        ['土屋訪問介護事業所 和歌山', '和歌山', 'ツチヤホウモンカイゴジギョウショワカヤマ'],
        ['土屋訪問介護事業所 吹田', '吹田', 'ツチヤホウモンカイゴジギョウショスイタ'],
        ['土屋訪問介護事業所 北九州', '北九州', 'ツチヤホウモンカイゴジギョウショキタキュウシュウ'],
        ['土屋訪問介護事業所 久留米', '久留米', 'ツチヤホウモンカイゴジギョウショクルメ'],
        ['土屋訪問介護事業所 佐賀', '佐賀', 'ツチヤホウモンカイゴジギョウショサガ'],
        ['土屋訪問介護事業所 長崎', '長崎', 'ツチヤホウモンカイゴジギョウショナガサキ'],
        ['土屋訪問介護事業所 熊本', '熊本', 'ツチヤホウモンカイゴジギョウショクマモト'],
        ['不動前ヘルパーステーション', '不動前ヘルパー', 'フドウマエヘルパーステーション'],
    ];

    /**
     * 事業所名を生成する.
     *
     * @return string[]
     */
    public function officeName(): array
    {
        $name = Arr::random(self::OFFICE_NAMES);
        return [
            'name' => $name[0],
            'abbr' => $name[1],
            'phonetic_name' => $name[2],
        ];
    }
}
