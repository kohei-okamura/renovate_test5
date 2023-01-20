<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunkImpl as Chunk;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkComposeMixin} Test.
 */
final class DwsVisitingCareForPwsdChunkComposeMixinTest extends Test
{
    use CarbonMixin;
    use DwsVisitingCareForPwsdChunkTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_compose(): void
    {
        $this->should(
            'return composed instance when composable chunk given',
            function (Chunk $thisChunk, Chunk ...$chunks): void {
                $actual = Seq::from(...$chunks)->fold($thisChunk, fn (Chunk $z, Chunk $x): Chunk => $z->compose($x));
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->makeComposableExamples()]
        );
        $this->should(
            'throw LogicException when uncomposable chunk given',
            function (Chunk $thisChunk, Chunk $thatChunk): void {
                $this->assertThrows(LogicException::class, function () use ($thisChunk, $thatChunk): void {
                    $thisChunk->compose($thatChunk);
                });
            },
            ['examples' => $this->makeUncomposableExamples()]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_isComposable(): void
    {
        $this->should(
            'return true when composable chunk given',
            function (Chunk $thisChunk, Chunk $thatChunk): void {
                $actual = $thisChunk->isComposable($thatChunk);
                $this->assertTrue($actual);
            },
            ['examples' => $this->makeComposableExamples()]
        );
        $this->should(
            'return false when uncomposable chunk given',
            function (Chunk $thisChunk, Chunk $thatChunk): void {
                $actual = $thisChunk->isComposable($thatChunk);
                $this->assertFalse($actual);
            },
            ['examples' => $this->makeUncomposableExamples()]
        );
    }

    /**
     * 合成できる組合せを生成する.
     *
     * @return array|array[]|\Domain\Billing\DwsVisitingCareForPwsdChunkImpl[][]
     */
    private function makeComposableExamples(): array
    {
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // 時間範囲が重複も連続もしていない　※移動加算なし
            '1' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 14, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                ),
            ],

            // 時間範囲が重複も連続もしていない　※移動加算あり
            '2' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 14, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                    120
                ),
            ],

            // 時間範囲が連続している　※移動加算なし
            '3' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 8, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 16, 0),
                    Carbon::create(2021, 2, 11, 24, 0),
                ),
            ],

            // 時間範囲が連続している　※移動加算あり
            '4' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 8, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 16, 0),
                    Carbon::create(2021, 2, 11, 24, 0),
                    120
                ),
            ],

            // 時間範囲が重複（2人目が途中で合流 → 2人目が途中で帰る）　※移動加算なし
            '5' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                ),
            ],

            // 時間範囲が重複（2人目が途中で合流 → 2人目が途中で帰る）　※移動加算あり
            '6' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                    120
                ),
            ],

            // 時間範囲が重複（2人目が途中で合流 → 1人目が途中で帰る）　※移動加算なし
            '7' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                ),
            ],

            // 時間範囲が重複（2人目が途中で合流 → 1人目が途中で帰る）　※移動加算あり
            '8' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                    120
                ),
            ],

            // 時間範囲が重複（1人目が途中で合流 → 1人目が途中で帰る）　※移動加算なし
            '9' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                ),
            ],

            // 時間範囲が重複（1人目が途中で合流 → 1人目が途中で帰る）　※移動加算あり
            '10' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                    120
                ),
            ],

            // 時間範囲が重複（1人目が途中で合流 → 2人目が途中で帰る）　※移動加算なし
            '11' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                ),
            ],

            // 時間範囲が重複（1人目が途中で合流 → 2人目が途中で帰る）　※移動加算あり
            '12' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 15, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                    120
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 18, 0),
                    120
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.1
            // 1日に複数回提供
            '13' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 4, 0),
                    Carbon::create(2021, 2, 11, 7, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 8, 0),
                    Carbon::create(2021, 2, 11, 11, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.2
            // 移動あり
            '14' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 4, 0),
                    Carbon::create(2021, 2, 11, 7, 0),
                    180
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 7, 30),
                    Carbon::create(2021, 2, 11, 11, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 13, 0),
                    Carbon::create(2021, 2, 11, 16, 30),
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.3
            // 移動4時間以上
            '15' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 4, 0),
                    Carbon::create(2021, 2, 11, 7, 30),
                    210
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 9, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                    180
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 2, 11, 14, 0),
                    Carbon::create(2021, 2, 11, 17, 30),
                    210
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.4
            // 2人派遣同一時間
            '16' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 4, 0),
                    Carbon::create(2021, 3, 31, 7, 30),
                    ['headcount' => 2],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 9, 0),
                    Carbon::create(2021, 3, 31, 12, 0),
                    ['headcount' => 2],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 14, 0),
                    Carbon::create(2021, 3, 31, 17, 30),
                    ['headcount' => 2],
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.5
            // 2人派遣時間ずれ
            '17' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 3, 31, 4, 0),
                    Carbon::create(2021, 3, 31, 9, 0),
                    180
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 3, 31, 6, 0),
                    Carbon::create(2021, 3, 31, 12, 0),
                    180
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.6〜10
            // 合成を含まないためテストケースなし

            // インターフェース仕様書サービス提供実績記録票設定例 No.11
            // 二人派遣（移動介護）でサービス時間がずれた場合
            '18' => [
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 3, 31, 6, 0),
                    Carbon::create(2021, 3, 31, 10, 0),
                    240
                ),
                $this->makeChunkWithMoving(
                    Carbon::create(2021, 3, 31, 8, 0),
                    Carbon::create(2021, 3, 31, 12, 0),
                    240
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.12〜14
            // 合成を含まないためテストケースなし

            // インターフェース仕様書サービス提供実績記録票設定例 No.15
            // 二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）
            '19' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 8, 0),
                    Carbon::create(2021, 3, 31, 12, 0),
                    ['headcount' => 2, 'isCoaching' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 14, 0),
                    Carbon::create(2021, 3, 31, 16, 0),
                    ['headcount' => 2],
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.15 改
            // 二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）
            // 時間帯が連続している場合
            '20' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 10, 0),
                    Carbon::create(2021, 3, 31, 15, 0),
                    ['headcount' => 2, 'isCoaching' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 15, 0),
                    Carbon::create(2021, 3, 31, 20, 0),
                    ['headcount' => 2],
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.16
            // 二人派遣（熟練ヘルパーが一部の時間帯に新任ヘルパーに同行した場合）
            // 時間帯が連続している場合
            '21' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 8, 0),
                    Carbon::create(2021, 3, 31, 12, 0),
                    ['headcount' => 2, 'isCoaching' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 3, 31, 12, 0),
                    Carbon::create(2021, 3, 31, 16, 0),
                ),
            ],
        ];
    }

    /**
     * 合成できない組合せを生成する.
     *
     * @return array|array[]|\Domain\Billing\DwsVisitingCareForPwsdChunkImpl[][]
     */
    private function makeUncomposableExamples(): array
    {
        $chunk = $this->baseChunk;
        return [
            'have different categories' => [
                $chunk->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd1()]),
                $chunk->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd2()]),
            ],
            'have different providedOn' => [
                $chunk->copy(['providedOn' => Carbon::create(1995, 6, 4)]),
                $chunk->copy(['providedOn' => Carbon::create(1996, 6, 11)]),
            ],
        ];
    }

    /**
     * 指定した時間範囲を持つサービス単位（移動加算あり）を生成する.
     *
     * @param \Domain\Common\Carbon $start
     * @param \Domain\Common\Carbon $end
     * @param int $movingDurationMinutes
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunkImpl
     */
    private function makeChunkWithMoving(
        Carbon $start,
        Carbon $end,
        int $movingDurationMinutes
    ): Chunk {
        $range = CarbonRange::create(compact('start', 'end'));
        $nonMoving = $this->baseFragment->copy([
            'range' => $range,
        ]);
        $withMoving = $nonMoving->copy([
            'isMoving' => true,
            'movingDurationMinutes' => $movingDurationMinutes,
        ]);
        return $this->baseChunk->copy([
            'providedOn' => $start->startOfDay(),
            'range' => $range,
            'fragments' => Seq::from($nonMoving, $withMoving),
        ]);
    }
}
