<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Common\TimeRange;
use Lib\Exceptions\InvalidArgumentException;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * TimeRangeTest のテスト
 */
class TimeRangeTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('succeeds when data is passed as an argument', function (): void {
            $time_range = TimeRange::create($this->defaultInput());
            $this->assertInstanceOf(TimeRange::class, $time_range);
        });
        $this->should('fails when the data does not pass as an argument', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                TimeRange::create([]);
            });
        });
        $examples = [
            'when start is empty' => [
                ['start' => ''],
                ['start' => '10:00'],
            ],
            'when end is empty' => [
                ['end' => ''],
                ['end' => '21:34'],
            ],
            'when start is integer' => [
                ['start' => 1200],
                ['start' => '10:00'],
            ],
            'when end is not a time format' => [
                ['end' => '2100'],
                ['end' => '21:34'],
            ],
        ];
        $this->should(
            'fail if the data is not suitable as an argument',
            function ($invalid, $valid): void {
                $this->assertThrows(InvalidArgumentException::class, function () use ($invalid): void {
                    TimeRange::create($invalid + $this->defaultInput());
                });
                $time_range = TimeRange::create($valid + $this->defaultInput());
                $this->assertInstanceOf(TimeRange::class, $time_range);
            },
            compact('examples')
        );
    }

    /**
     * TimeRangeが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'start' => '10:00',
            'end' => '21:34',
        ];
    }
}
