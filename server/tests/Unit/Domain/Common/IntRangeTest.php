<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\IntRange;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Common\IntRange} Test.
 */
class IntRangeTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected IntRange $intRange;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (IntRangeTest $self): void {
            $self->values = [
                'start' => 10,
                'end' => 20,
            ];
            $self->intRange = IntRange::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'start' => ['start'],
            'end' => ['end'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->intRange->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->intRange);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isOverlapping(): void
    {
        $examples = [
            'return false when that range is before' => [
                5, 6, false,
            ],
            'return false when that range is after' => [
                21, 30, false,
            ],
            'return true when that range before is overlapping' => [
                5, 15, true,
            ],
            'return true when that range after is overlapping' => [
                15, 25, true,
            ],
            'return true when that range is in' => [
                12, 18, true,
            ],
            'return true when that range is covered' => [
                5, 25, true,
            ],
        ];
        $this->should('return specified parameter', function (int $start, int $end, bool $expect): void {
            $that = IntRange::create(compact('start', 'end'));
            $this->assertSame($expect, $this->intRange->isOverlapping($that));
        }, compact('examples'));
    }
}
