<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\ExcelTimestampRule} のテスト.
 */
final class ExcelTimestampRuleTest extends Test
{
    use ExamplesConsumer;
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
    public function describe_validateExcelTimestamp(): void
    {
        $this->should('pass when value is int', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 53211],
                    ['value' => 'excel_timestamp']
                )
                    ->passes()
            );
        });
        $this->should('pass when value is float', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => (float)53211.234],
                    ['value' => 'excel_timestamp']
                )
                    ->passes()
            );
        });
        $this->should('fail when value is string', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'hoge'],
                    ['value' => 'excel_timestamp']
                )
                    ->fails()
            );
        });
    }
}
