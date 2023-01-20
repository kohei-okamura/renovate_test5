<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkSplitMixin} のテスト.
 */
final class DwsVisitingCareForPwsdChunkSplitMixinTest extends Test
{
    use DwsVisitingCareForPwsdChunkTestSupport;
    use MatchesSnapshots;
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
    public function describe_split(): void
    {
        $this->should(
            'return a Seq contains 2 elements when when the service is spanning the 0 o\'clock',
            function (DwsVisitingCareForPwsdChunk $chunk): void {
                $actual = $chunk->split();
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->spanningExamples()]
        );
        $this->should(
            'return a Seq contains only itself when the the service is not spanning the 0 o\'clock',
            function (DwsVisitingCareForPwsdChunk $chunk): void {
                $actual = $chunk->split();
                $this->assertCount(1, $actual);
                $this->assertSame($chunk, $actual->head());
            },
            ['examples' => $this->nonSpanningExamples()]
        );
    }

    /**
     * テスト用に用いる値の一覧（日跨ぎなし）を生成する.
     */
    private function nonSpanningExamples(): array
    {
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // インターフェース仕様書 設定例 No.1【1日に複数回提供】
            // 4:00〜7:00 + 8:00〜11:00 + 12:00〜15:00
            '1' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 1, 15, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 0, 0),
                    Carbon::create(2021, 4, 1, 11, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    Carbon::create(2021, 4, 1, 15, 0, 0),
                ),
            ),
            // インターフェース仕様書 設定例 No.2【移動あり】
            // 4:00〜7:00（移動加算あり） + 7:30〜11:00 + 13:00〜16:30
            '2' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 1, 16, 30, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 7, 30, 0),
                    Carbon::create(2021, 4, 1, 11, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 13, 0, 0),
                    Carbon::create(2021, 4, 1, 16, 30, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180]
                ),
            ),
            // インターフェース仕様書 設定例 No.3【移動4時間以上】
            // 4:00〜7:30（移動加算あり） + 9:00〜12:00（移動加算あり） + 14:00〜17:30（移動加算あり）
            '3' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 1, 17, 30, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 30, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 9, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 14, 0, 0),
                    Carbon::create(2021, 4, 1, 17, 30, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 30, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 210]
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 9, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180]
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 14, 0, 0),
                    Carbon::create(2021, 4, 1, 17, 30, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 210]
                ),
            ),
            // インターフェース仕様書 設定例 No.4【二人派遣同一時間】
            // 4:00〜7:30（2人） + 9:00〜12:00（2人） + 14:00〜17:30（2人）
            '4' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 1, 17, 30, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 7, 30, 0),
                    ['headcount' => 2]
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 9, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['headcount' => 2]
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 14, 0, 0),
                    Carbon::create(2021, 4, 1, 17, 30, 0),
                    ['headcount' => 2]
                ),
            ),
            // インターフェース仕様書 設定例 No.5【二人派遣時間ずれ】
            // 4:00〜9:00（1人目） + 6:00〜12:00（2人目） + 6:00〜9:00（移動加算あり・2人）
            '5' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 6, 0, 0),
                    Carbon::create(2021, 4, 1, 9, 0, 0),
                    ['isSecondary' => true],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 6, 0, 0),
                    Carbon::create(2021, 4, 1, 9, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180, 'isSecondary' => true],
                ),
            ),
            // インターフェース仕様書 設定例 No.11【二人派遣（移動介護）でサービス時間がずれた場合】
            // 6:00〜10:00（1人目・移動加算あり） + 8:00〜12:00（2人目・移動加算あり）
            '6' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 6, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 6, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 0, 0),
                    Carbon::create(2021, 4, 1, 10, 0, 0),
                    ['isSecondary' => true],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 6, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 360],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 0, 0),
                    Carbon::create(2021, 4, 1, 10, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 120, 'isSecondary' => true],
                ),
            ),
            // インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日以内
            // 6:00〜12:00
            '7' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 6, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isHospitalized' => true],
            ),
            // インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日超
            // 6:00〜12:00
            '8' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 6, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isLongHospitalized' => true],
            ),
            // インターフェース仕様書 設定例 No.14【二人派遣（熟練ヘルパーが同一時間帯に新任ヘルパーに同行した場合）】
            // 8:00〜12:00（同行・2人）
            '9' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 8, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isCoaching' => true, 'headcount' => 2],
            ),
            // インターフェース仕様書 設定例 No.15【二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）】
            // 8:00〜12:00（同行・2人） + 14:00〜16:00（2人）
            '10' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 8, 0, 0),
                Carbon::create(2021, 4, 1, 16, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['isCoaching' => true, 'headcount' => 2],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 14, 0, 0),
                    Carbon::create(2021, 4, 1, 16, 0, 0),
                    ['headcount' => 2],
                ),
            ),
            // インターフェース仕様書 設定例 No.16【二人派遣（熟練ヘルパーが一部の時間帯に新任ヘルパーに同行した場合）】
            // 8:00〜12:00（同行・2人） + 12:00〜16:00
            '11' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 8, 0, 0),
                Carbon::create(2021, 4, 1, 16, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 0, 0),
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    ['isCoaching' => true, 'headcount' => 2],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 12, 0, 0),
                    Carbon::create(2021, 4, 1, 16, 0, 0),
                ),
            ),
        ];
    }

    /**
     * テスト用に用いる値の一覧（日跨ぎあり）を生成する.
     */
    private function spanningExamples(): array
    {
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // インターフェース仕様書 設定例 No.6【13時間以上の提供で、かつ0時またがり】
            // 4:00〜翌1:00 + 移動介護180分
            '1' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 2, 1, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 1, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 1, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180],
                ),
            ),
            // インターフェース仕様書 設定例 No.7【13時間以上の提供で、かつ0時またがり二人派遣】
            // 4:00〜翌1:00 + 移動介護180分 × 2人
            '2' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 2, 1, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 1, 0, 0),
                    ['headcount' => 2],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 1, 0, 0),
                    ['headcount' => 2, 'isMoving' => true, 'movingDurationMinutes' => 180],
                ),
            ),
            // インターフェース仕様書 設定例 No.8【最小単位（30分）で0時またがり】
            // 21:45〜翌2:45
            '3' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 45, 0),
                Carbon::create(2021, 4, 2, 2, 45, 0),
            ),
            // インターフェース仕様書 設定例 No.9【0時またがり複数サービス】
            // 21:00〜翌0:30 + 翌1:30〜5:00
            // 2日目に開始するサービスはサービス単位（Chunk）生成時に同一サービス単位に含まれないため対象外
            '4' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 0, 0),
                Carbon::create(2021, 4, 2, 0, 30, 0),
            ),
            // インターフェース仕様書 設定例 No.10【0時またがりサービス終了】
            // 23:00〜翌0:30
            '5' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 0, 0),
                Carbon::create(2021, 4, 2, 0, 30, 0),
            ),
            // インターフェース仕様書 設定例 No.12【最初の1時間で0時またがり】
            // 23:45〜翌2:45
            '6' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 45, 0),
                Carbon::create(2021, 4, 2, 2, 45, 0),
            ),
        ];
    }
}
