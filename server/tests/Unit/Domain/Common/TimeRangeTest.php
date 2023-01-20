<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\TimeRange;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * TimeRange のテスト
 */
class TimeRangeTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected TimeRange $timeRange;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (TimeRangeTest $self): void {
            $self->values = [
                'start' => '10:00',
                'end' => '11:00',
            ];
            $self->timeRange = TimeRange::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have start attribute', function (): void {
            $this->assertSame($this->timeRange->get('start'), Arr::get($this->values, 'start'));
        });
        $this->should('have end attribute', function (): void {
            $this->assertSame($this->timeRange->get('end'), Arr::get($this->values, 'end'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->timeRange);
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
                '09:00',
                '09:30',
                false,
            ],
            'return false when that range is after' => [
                '11:30',
                '12:00',
                false,
            ],
            'return true when that range before is overlapping' => [
                '09:00',
                '10:30',
                true,
            ],
            'return true when that range after is overlapping' => [
                '10:30',
                '12:00',
                true,
            ],
            'return true when that range is in' => [
                '10:15',
                '10:45',
                true,
            ],
            'return true when that range is covered' => [
                '09:30',
                '11:30',
                true,
            ],
        ];
        $this->should('return specified parameter', function (string $start, string $end, bool $expect): void {
            $that = TimeRange::create(compact('start', 'end'));
            $this->assertSame($expect, $this->timeRange->isOverlapping($that));
        }, compact('examples'));
    }

    /**
     * @test
     * @return void
     */
    public function describe_toMinutes(): void
    {
        $this->should('start is greater than end', function (): void {
            $assert = TimeRange::create(['start' => '01:00', 'end' => '02:15'])->toMinutes();
            $expect = 75;
            $this->assertSame($expect, $assert);
        });
        $this->should('end is greater than start', function (): void {
            $assert = TimeRange::create(['start' => '02:00', 'end' => '01:00'])->toMinutes();
            $expect = 1380;
            $this->assertSame($expect, $assert);
        });
    }
}
