<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\AsciiAlphaNumRule} のテスト.
 */
final class AsciiAlphaNumRuleTest extends Test
{
    use MatchesSnapshots;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateAsciiAlphaNum(): void
    {
        $this->should(
            'pass',
            function ($value): void {
                $validator = $this->buildCustomValidator(
                    ['value' => $value],
                    ['value' => 'ascii_alpha_num'],
                );

                $this->assertTrue($validator->passes());
            },
            [
                'examples' => [
                    'with alphabet' => ['12a34'],
                    'with alphabet only' => ['abcde'],
                    'with number only' => ['12345'],
                ],
            ]
        );
        $this->should(
            'fail',
            function ($value): void {
                $validator = $this->buildCustomValidator(
                    ['value' => $value],
                    ['value' => 'ascii_alpha_num'],
                );

                $this->assertTrue($validator->fails());
            },
            [
                'examples' => [
                    'with not string' => [false],
                    'with hiragana' => ['12あ34'],
                    'with katakana' => ['12ア34'],
                    'with kanji' => ['12亜34'],
                    'with invalid character' => ['123_4'],
                ],
            ]
        );
    }
}
