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
 * {@link \App\Validations\Rules\EqualToLengthOfRule} のテスト.
 */
final class EqualToLengthOfRuleTest extends Test
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
    public function describe_validateEqualToLengthOf(): void
    {
        $this->should('pass rule', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => 3,
                        'array' => [1, 2, 3],
                    ],
                    ['value' => 'equal_to_length_of:array'],
                )->passes()
            );
        });
        $this->should('fail rule', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => 3,
                        'array' => [1, 2],
                    ],
                    ['value' => 'equal_to_length_of:array'],
                )->fails()
            );
        });
        $this->should('return false when parameter is non-array', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'value' => 1,
                        'array' => 1,
                    ],
                    ['value' => 'equal_to_length_of:array'],
                )->fails()
            );
        });
    }
}
