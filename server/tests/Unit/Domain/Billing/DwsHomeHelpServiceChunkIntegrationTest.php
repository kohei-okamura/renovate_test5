<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk as Chunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Schedule;
use Domain\ProvisionReport\DwsProvisionReportItem;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceChunk} 関連の統合テスト.
 *
 * 生成から、Duration組み立てまでを一連の流れで検証する
 */
final class DwsHomeHelpServiceChunkIntegrationTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * ProvisionReportItem からのテスト.
     *
     * @test
     * @return void
     */
    public function test_fromProvisionReportItem(): void
    {
        $this->should(
            'run with physical care',
            function (CarbonRange ...$ranges): void {
                $baseItem = $this->examples->dwsProvisionReports[0]->results[0]->copy([
                    'headcount' => 1,
                    'movingDurationMinutes' => 0,
                    'options' => [],
                ]);
                $rangeSeq = Seq::fromArray($ranges);
                /** @var \Domain\Common\Carbon $providedOn */
                $providedOn = $rangeSeq->map(fn (CarbonRange $x): Carbon => $x->start)->min();
                $results = $rangeSeq->map(fn (CarbonRange $x): DwsProvisionReportItem => $baseItem->copy([
                    'schedule' => Schedule::create([
                        'date' => $providedOn,
                        'start' => $x->start,
                        'end' => $x->end,
                    ]),
                ]));
                $provisionReport = $this->examples->dwsProvisionReports[0]->copy([
                    'providedIn' => $providedOn->startOfMonth(),
                    'results' => $results->toArray(),
                    'fixedAt' => Carbon::create(2021, 3, 5),
                ]);
                $baseChunk = DwsHomeHelpServiceChunkImpl::from($provisionReport, $results[0]);
                $chunk = $results->size() === 1
                    ? $baseChunk
                    : $results->drop(1)->fold(
                        $baseChunk,
                        function (Chunk $z, DwsProvisionReportItem $x) use ($provisionReport): Chunk {
                            $xChunk = DwsHomeHelpServiceChunkImpl::from($provisionReport, $x);
                            return $z->compose($xChunk);
                        }
                    );

                $actual = $chunk->getDurations();

                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => self::createExamples()]
        );
    }

    /**
     * テスト用データを生成する.
     *
     * @return array
     */
    private static function createExamples(): array
    {
        return [
            'Mr.Ishimaru error' => [
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 1, 17, 25),
                    'end' => Carbon::create(2021, 2, 1, 20, 55),
                ]),
            ],
            'Timeframe spanning' => [
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 2, 17, 0),
                    'end' => Carbon::create(2021, 2, 2, 20, 0),
                ]),
            ],
            'Timeframe spanning with first-frame over 15min' => [
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 3, 7, 45),
                    'end' => Carbon::create(2021, 2, 3, 9, 15),
                ]),
            ],
            'Timeframe spanning with border time under 15min' => [
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 4, 16, 50),
                    'end' => Carbon::create(2021, 2, 4, 18, 50),
                ]),
            ],
            '3 Timeframes in durations' => [ // 増分型
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 5, 16, 0),
                    'end' => Carbon::create(2021, 2, 5, 23, 0),
                ]),
            ],
            'Spanning provided' => [ // 日跨ぎ型
                CarbonRange::create([
                    'start' => Carbon::create(2021, 2, 6, 23, 0),
                    'end' => Carbon::create(2021, 2, 7, 7, 0),
                ]),
            ],
            '2 Provision items with 1 break time.' => [
                CarbonRange::create([
                    'start' => Carbon::create(2021, 4, 13, 5, 30),
                    'end' => Carbon::create(2021, 4, 13, 7, 30),
                ]),
                CarbonRange::create([
                    'start' => Carbon::create(2021, 4, 13, 9, 0),
                    'end' => Carbon::create(2021, 4, 13, 11, 0),
                ]),
            ],
        ];
    }
}
