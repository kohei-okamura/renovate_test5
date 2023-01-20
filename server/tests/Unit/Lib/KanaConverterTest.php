<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Lib;

use Lib\KanaConverter;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Lib\KanaConverter} のテスト.
 */
final class KanaConverterTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_lowerCaseToUpperCase(): void
    {
        $this->should('return uppsercase katakana', function (): void {
            $notConvert = 'アイウエオカキクケコガギグゲゴサシスセソザジズゼゾタチツテトダヂヅデドナニヌネノハヒフヘホバビブベボパピプペポマミムメモヤユヨラリルレロワヲン';
            $value = $notConvert . 'ァィゥェォッャュョヮヵㇰヶㇱㇲㇳㇴㇵㇶㇷㇸㇹㇺㇻㇼㇽㇾㇿヮ';
            $expected = $notConvert . 'アイウエオツヤユヨワカクケシストヌハヒフヘホムラリルレロワ';
            $this->assertSame($expected, KanaConverter::lowerCaseToUpperCase($value));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toUppercaseHalfWidthKatakana(): void
    {
        $this->should('return uppsercase half-width katakana', function (): void {
            $this->assertSame('ﾌｱﾝ ｼﾞﾕﾝﾍﾟｲ', KanaConverter::toUppercaseHalfWidthKatakana('ファン ジュンペイ'));
            $this->assertSame('ﾔﾏﾓﾄ ｿﾌｲｱ', KanaConverter::toUppercaseHalfWidthKatakana('ヤマモト ソフィア'));
            $this->assertSame('ｱﾝﾄﾞｳ ﾘﾕｳｲﾁ', KanaConverter::toUppercaseHalfWidthKatakana('アンドゥ リュウイチ'));
            $this->assertSame('ﾁｴﾝ ﾏｵ', KanaConverter::toUppercaseHalfWidthKatakana('チェン マオ'));
            $this->assertSame('ｸﾞｵﾝ ｼﾞﾛｳ', KanaConverter::toUppercaseHalfWidthKatakana('グォン ジロウ'));
            $this->assertSame('ｶﾜｻﾜ ﾒｸﾞﾐ', KanaConverter::toUppercaseHalfWidthKatakana('ヵワサワ メグミ'));
            $this->assertSame('ｷﾝｼﾞﾖｳ ｼﾝｽｹ', KanaConverter::toUppercaseHalfWidthKatakana('キンジョウ シンスヶ'));
            $this->assertSame('ｵﾂﾊﾟﾀ ｱﾔﾉ', KanaConverter::toUppercaseHalfWidthKatakana('オッパタ アヤノ'));
            $this->assertSame('ﾛﾂﾋﾟﾔｸﾀﾞ ﾀｶｼ', KanaConverter::toUppercaseHalfWidthKatakana('ロッピャクダ タカシ'));
            $this->assertSame('ﾓﾝｼﾞﾕｶﾞﾜ ｴﾂｺ', KanaConverter::toUppercaseHalfWidthKatakana('モンジュガワ エツコ'));
            $this->assertSame('ｲﾂﾁﾖｳﾀﾞ ｱﾔｶ', KanaConverter::toUppercaseHalfWidthKatakana('イッチョウダ アヤカ'));
            $this->assertSame('ﾜﾀﾍﾞ ｼﾕﾝｲﾁﾛｳ', KanaConverter::toUppercaseHalfWidthKatakana('ヮタベ シュンイチロウ'));
        });
    }
}
