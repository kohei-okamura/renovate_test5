<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\KatakanaRule} のテスト.
 */
final class KatakanaRuleTest extends Test
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
    public function describe_validateKatakana(): void
    {
        $this->should('pass rule', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ユースタイルラボラトリー'],
                    ['value' => 'katakana'],
                )->passes()
            );
        });
        $this->should('fail rule', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'ユースタイルラボラトリ株式会社'],
                    ['value' => 'katakana'],
                )->fails()
            );
        });
    }
}
