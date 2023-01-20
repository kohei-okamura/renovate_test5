<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\LtcsProjectAmount} のテスト
 */
class LtcsProjectAmountTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProjectAmount $ltcsProjectAmount;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectAmountTest $self): void {
            $self->values = [
                'category' => LtcsProjectAmountCategory::housework(),
                'amount' => 60,
            ];
            $self->ltcsProjectAmount = LtcsProjectAmount::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'category' => ['category'],
            'amount' => ['amount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProjectAmount->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsProjectAmount);
        });
    }
}
