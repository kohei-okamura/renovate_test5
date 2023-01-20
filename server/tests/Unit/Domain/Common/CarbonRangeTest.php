<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Common\CarbonRange} Test.
 */
class CarbonRangeTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected CarbonRange $carbonRange;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CarbonRangeTest $self): void {
            $self->values = [
                'start' => Carbon::create('2020-01-01'),
                'end' => Carbon::create('2020-12-31'),
            ];
            $self->carbonRange = CarbonRange::create($self->values);
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
                $this->assertSame($this->carbonRange->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->carbonRange);
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
                Carbon::create('2019-01-01'),
                Carbon::create('2019-12-31'),
                false,
            ],
            'return false when that range is after' => [
                Carbon::create('2021-01-01'),
                Carbon::create('2021-12-31'),
                false,
            ],
            'return true when that range before is overlapping' => [
                Carbon::create('2019-12-01'),
                Carbon::create('2020-01-31'),
                true,
            ],
            'return true when that range after is overlapping' => [
                Carbon::create('2020-12-01'),
                Carbon::create('2021-01-31'),
                true,
            ],
            'return true when that range is in' => [
                Carbon::create('2020-04-01'),
                Carbon::create('2020-05-31'),
                true,
            ],
            'return true when that range is covered' => [
                Carbon::create('2019-12-01'),
                Carbon::create('2021-01-31'),
                true,
            ],
        ];
        $this->should('return specified parameter', function (Carbon $start, Carbon $end, bool $expect): void {
            $that = CarbonRange::create(compact('start', 'end'));
            $this->assertSame($expect, $this->carbonRange->isOverlapping($that));
        }, compact('examples'));
        $this->should('return false when containBoundary is set false and CarbonRange is consecutive', function (): void {
            $this->assertFalse(
                CarbonRange::create([
                    'start' => Carbon::create('2020-04-01'),
                    'end' => Carbon::create('2020-04-02'),
                ])->isOverlapping(CarbonRange::create([
                    'start' => Carbon::create('2020-04-02'),
                    'end' => Carbon::create('2020-04-03'),
                ]), false)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_intersection(): void
    {
        $examples = [
            'when that range is before' => [
                CarbonRange::create([
                    'start' => Carbon::create('2019-01-01'),
                    'end' => Carbon::create('2019-12-31'),
                ]),
                Option::none(),
            ],
            'when that range is after' => [
                CarbonRange::create([
                    'start' => Carbon::create('2021-01-01'),
                    'end' => Carbon::create('2021-12-31'),
                ]),
                Option::none(),
            ],
            'when that range before is overlapping' => [
                CarbonRange::create([
                    'start' => Carbon::create('2019-12-01'),
                    'end' => Carbon::create('2020-01-31'),
                ]),
                Option::some(CarbonRange::create([
                    'start' => Carbon::create('2020-01-01'),
                    'end' => Carbon::create('2020-01-31'),
                ])),
            ],
            'return true when that range after is overlapping' => [
                CarbonRange::create([
                    'start' => Carbon::create('2020-12-01'),
                    'end' => Carbon::create('2021-01-31'),
                ]),
                Option::some(CarbonRange::create([
                    'start' => Carbon::create('2020-12-01'),
                    'end' => Carbon::create('2020-12-31'),
                ])),
            ],
            'return true when that range is in' => [
                CarbonRange::create([
                    'start' => Carbon::create('2020-04-01'),
                    'end' => Carbon::create('2020-05-31'),
                ]),
                Option::some(CarbonRange::create([
                    'start' => Carbon::create('2020-04-01'),
                    'end' => Carbon::create('2020-05-31'),
                ])),
            ],
            'return true when that range is covered' => [
                CarbonRange::create([
                    'start' => Carbon::create('2019-12-01'),
                    'end' => Carbon::create('2021-01-31'),
                ]),
                Option::some(CarbonRange::create([
                    'start' => Carbon::create('2020-01-01'),
                    'end' => Carbon::create('2020-12-31'),
                ])),
            ],
        ];
        $this->should(
            'return intersected CarbonRange',
            function (CarbonRange $that, Option $expected): void {
                $this->assertModelStrictEquals(
                    $expected->getOrElseValue(CarbonRange::create()),
                    $this->carbonRange->intersection($that)->getOrElseValue(CarbonRange::create()),
                );
            },
            compact('examples')
        );
    }
}
