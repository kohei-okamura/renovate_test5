<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\ZenginDataRecordCharRule} のテスト.
 */
final class ZenginDataRecordCharRuleTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateZenginDataRecordChar(): void
    {
        $this->should('pass when halfwidth number', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '0000'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth number', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '００００'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when lower alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'asdf'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when upper alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ASDF'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth lower alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ａｂｃｄ'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth upper alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ＡＢＣＤ'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth upper alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ＡＢＣＤ'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth alphabet', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '００００'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when halfwidth katakana', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ｱｲｳｴｵ'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when fullwidth katakana', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'アイウエオ'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when space', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => ' 　'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when brackets', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '(）｢」'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when slash', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '/／'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when dot', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '．.'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when hyphen', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '-'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when yen mark', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '¥￥'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('pass when composite pattern', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => '0０aAＡＺアｱァ 　(）「｣.．-¥￥'],
                    ['value' => 'zengin_data_record_char'],
                )->passes()
            );
        });
        $this->should('fail rule', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ユースタイルラボラトリ株式会社'],
                    ['value' => 'zengin_data_record_char'],
                )->fails()
            );
        });
    }
}
