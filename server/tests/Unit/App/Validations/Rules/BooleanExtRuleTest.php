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
 * {@link \App\Validations\Rules\BooleanExtRule} のテスト.
 */
final class BooleanExtRuleTest extends Test
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
    public function describe_validateBooleanExt(): void
    {
        $this->should('pass valid boolean', function ($value) {
            $validator = $this->buildCustomValidator(
                ['value' => $value],
                ['value' => 'boolean_ext'],
            );
            $this->assertTrue($validator->passes());
        }, ['examples' => [
            'bool `true`' => [true],
            'bool `false`' => [false],
            'int 0' => [0],
            'int 1' => [1],
            'string 0' => ['0'],
            'string 1' => ['1'],
            'string `true`' => ['true'],
            'string `false`' => ['false'],
        ]]);
        $this->should('failed invalid type input', function ($value) {
            $validator = $this->buildCustomValidator(
                ['value' => $value],
                ['value' => 'boolean_ext'],
            );
            $this->assertTrue($validator->fails());
        }, ['examples' => [
            'string' => ['string'],
            'int' => [5],
        ]]);
    }
}
