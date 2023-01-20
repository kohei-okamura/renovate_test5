<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunkImpl as Chunk;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceChunkComposeMixin} のテスト.
 */
final class DwsHomeHelpServiceChunkComposeMixinTest extends Test
{
    use CarbonMixin;
    use DwsHomeHelpServiceChunkTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
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
            // 時間範囲が重複している
            '1' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                ),
            ],

            // 時間範囲が連続している
            '2' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 16, 0),
                    Carbon::create(2021, 2, 11, 20, 0),
                ),
            ],

            // 時間範囲が重複（包含）している
            '3' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 8, 0),
                    Carbon::create(2021, 2, 11, 16, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                ),
            ],

            // 時間範囲が連続も重複もしていない（2時間ルール）
            '4' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 19, 0),
                    Carbon::create(2021, 2, 11, 21, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 22, 0),
                    Carbon::create(2021, 2, 11, 23, 0),
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.4【同一時間2人派遣】
            // headcount = 2 ではなく別々に予実を登録した場合を想定
            '5' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 11, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 11, 0),
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.5【2人派遣時間ずれ】
            '6' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 11, 0),
                    Carbon::create(2021, 2, 11, 13, 0),
                ),
            ],
            // インターフェース仕様書サービス提供実績記録票設定例 No.6【2人派遣ヘルパー要件違い】
            '7' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                    [],
                    ['providerType' => DwsHomeHelpServiceProviderType::none()],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 11, 0),
                    Carbon::create(2021, 2, 11, 13, 0),
                    [],
                    ['providerType' => DwsHomeHelpServiceProviderType::beginner()],
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.8【空き時間あり】
            '8' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 5, 0),
                    Carbon::create(2021, 2, 11, 7, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 8, 30),
                    Carbon::create(2021, 2, 11, 10, 0),
                ),
            ],

            // インターフェース仕様書サービス提供実績記録票設定例 No.9【空き時間複数あり】
            '9' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 5, 0),
                    Carbon::create(2021, 2, 11, 6, 15),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 6, 30),
                    Carbon::create(2021, 2, 11, 7, 30),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 8, 30),
                    Carbon::create(2021, 2, 11, 10, 0),
                ),
            ],

            // DEV-4979 検証
            '10' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 11, 13, 0),
                    Carbon::create(2021, 4, 11, 17, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 11, 15, 0),
                    Carbon::create(2021, 4, 11, 19, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 11, 18, 0),
                    Carbon::create(2021, 4, 11, 20, 0),
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
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // サービス内容が一致しない
            '1' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),
            ],

            // 建物区分が一致しない
            '2' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['buildingType' => DwsHomeHelpServiceBuildingType::none()],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['buildingType' => DwsHomeHelpServiceBuildingType::over20()],
                ),
            ],

            // 合成先が緊急対応
            '3' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['isEmergency' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['isEmergency' => false],
                ),
            ],

            // 合成対象が緊急対応
            '4' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['isEmergency' => false],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['isEmergency' => true],
                ),
            ],

            // 合成先・合成対象の双方がが緊急対応
            '5' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['isEmergency' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['isEmergency' => true],
                ),
            ],

            // 初計フラグが一致しない
            '6' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 15, 0),
                    ['isPlannedByNovice' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    ['isPlannedByNovice' => false],
                ),
            ],

            // 時間範囲の差が2時間超（2時間ルールの対象外）
            '7' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 5, 0),
                    Carbon::create(2021, 2, 11, 9, 0),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 11, 5),
                    Carbon::create(2021, 2, 11, 15, 5),
                ),
            ],

            // 合成対象のサービス単位（Chunk）が複数の要素（Fragment）を持っている
            '8' => [
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 2, 11, 10, 0),
                    Carbon::create(2021, 2, 11, 12, 0),
                ),
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 2, 11, 12, 0),
                    Carbon::create(2021, 2, 11, 17, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 2, 11, 12, 0),
                        Carbon::create(2021, 2, 11, 14, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 2, 11, 14, 0),
                        Carbon::create(2021, 2, 11, 17, 0),
                    ),
                ),
            ],
        ];
    }
}
