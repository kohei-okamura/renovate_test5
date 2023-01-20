<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkGetDurationsMixin} のテスト.
 */
final class DwsVisitingCareForPwsdChunkGetDurationsMixinTest extends Test
{
    use DwsVisitingCareForPwsdChunkTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    private DwsVisitingCareForPwsdChunkImpl $dwsVisitingCareForPwsdChunk;

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
    public function describe_getDurations(): void
    {
        $this->should(
            'return expected durations',
            function (DwsVisitingCareForPwsdChunk $chunk): void {
                $this->assertMatchesModelSnapshot([
                    'chunk' => $chunk,
                    'durations' => $chunk->getDurations(),
                ]);
            },
            ['examples' => $this->examples()]
        );
    }

    /**
     * テスト用に用いる値の一覧を生成する.
     */
    private function examples(): array
    {
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // インターフェース仕様書 設定例 No.1【1日に複数回提供】
            // 4:00〜7:00 + 8:00〜11:00 + 12:00〜15:00
            // -> 深夜120分 + 早朝60分 + 日中360分
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
            // -> 深夜120分 + 早朝90分 + 日中390分 + 移動180分
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
            // -> 深夜120分 + 早朝90分 + 日中390分 + 移動600分
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
            // -> 深夜120分 + 早朝90分 + 日中390分
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
            // -> 深夜120分 + 早朝120分 × 2人 + 日中60分 + 日中240分 + 移動180分×2人
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

            // インターフェース仕様書 設定例 No.6【13時間以上の提供で、かつ0時またがり】
            // 4:00〜翌1:00 + 移動介護180分
            // -> 深夜120分 + 早朝120分 + 日中600分 + 夜間240分 + 深夜120分 + 移動180分
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:00 までをテスト
            '6' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 2, 0, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                    ['isMoving' => true, 'movingDurationMinutes' => 180],
                ),
            ),

            // インターフェース仕様書 設定例 No.7【13時間以上の提供で、かつ0時またがり二人派遣】
            // 4:00〜翌1:00 + 移動介護180分 × 2人
            // -> 深夜120分（2人） + 早朝120分（2人） + 日中600分（2人） + 夜間240分（2人） + 深夜120分（2人） + 移動180分（2人）
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:00 までをテスト
            '7' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 4, 0, 0),
                Carbon::create(2021, 4, 2, 0, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                    ['headcount' => 2],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 4, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                    ['headcount' => 2, 'isMoving' => true, 'movingDurationMinutes' => 180],
                ),
            ),

            // インターフェース仕様書 設定例 No.8【最小単位（30分）で0時またがり】
            // 21:45〜翌2:45（1日目135分 + 2日目165分）-> 深夜150分
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:15 までをテスト
            '8' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 45, 0),
                Carbon::create(2021, 4, 2, 0, 15, 0),
            ),

            // インターフェース仕様書 設定例 No.9【0時またがり複数サービス】
            // 21:00〜翌0:30 + 翌1:30〜5:00（1日目180分 + 240分）-> 夜間60分 + 深夜120分
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:00 までをテスト
            '9' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 0, 0),
                Carbon::create(2021, 4, 2, 0, 0, 0),
            ),

            // インターフェース仕様書 設定例 No.10【0時またがりサービス終了】
            // 23:00〜翌0:30（1日目60分 + 2日目30分）-> 深夜60分
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:00 までをテスト
            '10' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 23, 0, 0),
                Carbon::create(2021, 4, 2, 0, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 23, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                ),
            ),

            // インターフェース仕様書 設定例 No.11【二人派遣（移動介護）でサービス時間がずれた場合】
            // 6:00〜10:00（1人目・移動加算あり） + 8:00〜12:00（2人目・移動加算あり）
            // -> 早朝120分 + 日中360分 + 日中120分 + 移動360分 + 移動120分
            '11' => $this->makeChunkWithFragments(
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

            // インターフェース仕様書 設定例 No.12【最初の1時間で0時またがり】
            // 23:45〜翌2:45
            // -> 深夜60分
            // ※本件システムでは `DwsVisitingCareForPwsdChunk` 生成の段階で0時またがりを処理しているため 0:45 までをテスト
            '12' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 45, 0),
                Carbon::create(2021, 4, 2, 0, 45, 0),
            ),

            // インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日以内
            // 6:00〜12:00
            // -> 早朝120分 + 日中240分
            '13' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 6, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isHospitalized' => true],
            ),

            // インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日超
            // 6:00〜12:00
            // -> 早朝120分 + 日中240分
            '14' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 6, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isLongHospitalized' => true],
            ),

            // インターフェース仕様書 設定例 No.14【二人派遣（熟練ヘルパーが同一時間帯に新任ヘルパーに同行した場合）】
            // 8:00〜12:00（同行・2人）
            // -> 日中240分（同行・2人）
            '15' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 8, 0, 0),
                Carbon::create(2021, 4, 1, 12, 0, 0),
                ['isCoaching' => true, 'headcount' => 2],
            ),

            // インターフェース仕様書 設定例 No.15【二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）】
            // 8:00〜12:00（同行・2人） + 14:00〜16:00（2人）
            // -> 日中240分（同行・2人） + 日中120分（2人）
            '16' => $this->makeChunkWithFragments(
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
            // -> 日中240分（同行・2人） + 日中240分
            '17' => $this->makeChunkWithFragments(
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

            // 最初の1時間で時間帯を跨ぐ場合（開始時間帯の占める割合：50%以上）
            // 7:25〜15:25（早朝30分 + 日中450分）-> 早朝60分 + 日中420分
            '18' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 7, 25, 0),
                Carbon::create(2021, 4, 1, 15, 25, 0),
            ),

            // 最初の1時間で時間帯を跨ぐ場合（開始時間帯の占める割合：50%）
            // 7:30〜15:30（早朝30分 + 日中450分）-> 早朝60分 + 日中420分
            '19' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 7, 30, 0),
                Carbon::create(2021, 4, 1, 15, 30, 0),
            ),

            // 最初の1時間で時間帯を跨ぐ場合（開始時間帯の占める割合：50%未満）
            // 7:35〜15:35（早朝25分 + 日中455分）-> 日中480分
            '20' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 7, 35, 0),
                Carbon::create(2021, 4, 1, 15, 35, 0),
            ),

            // 最小単位（30分）で時間帯を跨ぐ場合（開始時間帯の占める割合：50%以上）
            // 15:40〜23:40（日中140分 + 夜間240分 + 深夜100分）
            // -> 日中150分 + 夜間240分 + 深夜90分
            '21' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 15, 40, 0),
                Carbon::create(2021, 4, 1, 23, 40, 0),
            ),

            // 最小単位（30分）で時間帯を跨ぐ場合（開始時間帯の占める割合：50%）
            // 15:45〜23:45（日中135分 + 夜間240分 + 深夜105分）
            // -> 日中150分 + 夜間240分 + 深夜90分
            '22' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 15, 45, 0),
                Carbon::create(2021, 4, 1, 23, 45, 0),
            ),

            // 最小単位（30分）で時間帯を跨ぐ場合（開始時間帯の占める割合：50%未満）
            // 15:50〜23:50（日中130分 + 夜間240分 + 深夜110分）
            // -> 日中120分 + 夜間240分 + 深夜120分
            '23' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 15, 50, 0),
                Carbon::create(2021, 4, 1, 23, 50, 0),
            ),

            // 非常に短い（40分）かつ時間帯を跨ぐ場合（開始時間帯の占める割合：50%以上）
            // 5:40〜6:20（深夜25分 + 早朝15分）-> 深夜40分
            '24' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 35, 0),
                Carbon::create(2021, 4, 1, 6, 15, 0),
            ),

            // 非常に短い（40分）かつ時間帯を跨ぐ場合（開始時間帯の占める割合：50%）
            // 5:40〜6:20（深夜20分 + 早朝20分）-> 深夜40分
            '25' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 40, 0),
                Carbon::create(2021, 4, 1, 6, 20, 0),
            ),

            // 非常に短い（40分）かつ時間帯を跨ぐ場合（開始時間帯の占める割合：50%未満）
            // 5:45〜6:25（深夜15分 + 早朝25分）-> 早朝40分
            '26' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 45, 0),
                Carbon::create(2021, 4, 1, 6, 25, 0),
            ),

            // インターフェース仕様書 設定例 No.9【0時またがり複数サービス】2日目
            // 21:00〜翌0:30 + 翌1:30〜5:00-> 深夜240分
            '27' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 0, 0, 0),
                Carbon::create(2021, 4, 2, 5, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 0, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 30, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 1, 30, 0),
                    Carbon::create(2021, 4, 2, 5, 0, 0),
                ),
            ),

            // インターフェース仕様書 設定例 No.9【0時またがり複数サービス】2日目（改）DEV-5792
            // See https://eustylelab.backlog.com/view/DEV-5792#comment-74826168
            // 21:45〜翌0:30 + 翌1:30〜5:00-> 深夜240分
            '28' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 0, 15, 0),
                Carbon::create(2021, 4, 2, 5, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 0, 15, 0),
                    Carbon::create(2021, 4, 2, 0, 30, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 1, 30, 0),
                    Carbon::create(2021, 4, 2, 5, 0, 0),
                ),
            ),

            // DEV-6438 最初の時間帯が最小単位（1時間）に満たない場合: 30分で割り切れる
            '29' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 0, 0),
                Carbon::create(2021, 4, 2, 8, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 0, 0),
                    Carbon::create(2021, 4, 2, 0, 30),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 6, 0),
                    Carbon::create(2021, 4, 2, 8, 0),
                ),
            ),

            // DEV-6438 最初の時間帯が最小単位（1時間）に満たない場合: 最初の時間帯が30分未満
            '30' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 6, 0),
                Carbon::create(2021, 4, 2, 21, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 6, 0),
                    Carbon::create(2021, 4, 2, 6, 25),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 10, 0),
                    Carbon::create(2021, 4, 2, 12, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 20, 25),
                    Carbon::create(2021, 4, 2, 21, 0),
                ),
            ),

            // DEV-6438 最初の時間帯が最小単位（1時間）に満たない場合: 最初の時間帯が30分以上（ただし30分で割り切れない）
            '31' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 12, 0),
                Carbon::create(2021, 4, 2, 23, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 12, 0),
                    Carbon::create(2021, 4, 2, 12, 45),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 18, 45),
                    Carbon::create(2021, 4, 2, 23, 0),
                ),
            ),

            // DEV-6438 時間数が最小単位（30分）で割り切れない場合: 開始時刻が属する時間値が15分未満
            '32' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 6, 0),
                Carbon::create(2021, 4, 2, 23, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 6, 0),
                    Carbon::create(2021, 4, 2, 12, 10),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 18, 10),
                    Carbon::create(2021, 4, 2, 23, 0),
                ),
            ),

            // DEV-6438 時間数が最小単位（30分）で割り切れない場合: 開始時刻が属する時間値が15分以上かつ次の時間帯が短い
            '33' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 0, 0),
                Carbon::create(2021, 4, 2, 20, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 0, 0),
                    Carbon::create(2021, 4, 2, 7, 45),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 17, 45),
                    Carbon::create(2021, 4, 2, 20, 0),
                ),
            ),

            // DEV-6438 時間数が最小単位（30分）で割り切れない場合: 開始時刻が属する時間値が15分以上かつ次の時間帯が十分長い
            '34' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 2, 0, 0),
                Carbon::create(2021, 4, 2, 21, 0),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 0, 0),
                    Carbon::create(2021, 4, 2, 7, 45),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 2, 12, 15),
                    Carbon::create(2021, 4, 2, 21, 0),
                ),
            ),

            // DEV-6463 2人目が複数回現れるケース
            '35' => $this->makeChunkWithFragments(
                Carbon::create(2021, 6, 10, 0, 0),
                Carbon::create(2021, 6, 11, 0, 0),
                $this->makeFragment(
                    Carbon::create(2021, 6, 10, 0, 0),
                    Carbon::create(2021, 6, 10, 10, 0),
                    ['headcount' => 2],
                ),
                $this->makeFragment(
                    Carbon::create(2021, 6, 10, 16, 0),
                    Carbon::create(2021, 6, 11, 0, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 6, 10, 23, 0),
                    Carbon::create(2021, 6, 11, 0, 0),
                    ['isSecondary' => true],
                ),
            ),
        ];
    }
}
