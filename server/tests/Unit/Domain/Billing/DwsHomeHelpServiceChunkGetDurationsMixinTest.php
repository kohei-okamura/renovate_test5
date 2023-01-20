<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceChunkGetDurationsMixin} のテスト.
 */
final class DwsHomeHelpServiceChunkGetDurationsMixinTest extends Test
{
    use DwsHomeHelpServiceChunkTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    private DwsHomeHelpServiceChunkImpl $dwsHomeHelpServiceChunk;

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
            function (DwsHomeHelpServiceChunk $chunk): void {
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
            // インターフェース仕様書 設定例 No.1【通常】
            '1' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 10, 0),
                Carbon::create(2021, 4, 1, 11, 30),
            ),

            // インターフェース仕様書 設定例 No.2【ヘルパー要件あり】
            '2' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 10, 0),
                Carbon::create(2021, 4, 1, 12, 0),
                [],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),

            // インターフェース仕様書 設定例 No.3【乗降】
            // 運転には未対応のため未定義

            // インターフェース仕様書 設定例 No.4【同一時間2人派遣】
            '3' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 10, 0),
                Carbon::create(2021, 4, 1, 11, 0),
                [],
                ['headcount' => 2],
            ),

            // インターフェース仕様書 設定例 No.5【2人派遣時間ずれ】
            '4' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 10, 0),
                Carbon::create(2021, 4, 1, 13, 0),
                [],
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 10, 0),
                    Carbon::create(2021, 4, 1, 13, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 11, 0),
                    Carbon::create(2021, 4, 1, 12, 0),
                    ['isSecondary' => true],
                ),
            ),

            // インターフェース仕様書 設定例 No.6【2人派遣ヘルパー要件違い】
            '5' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 10, 0),
                Carbon::create(2021, 4, 1, 13, 0),
                [],
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 10, 0),
                    Carbon::create(2021, 4, 1, 12, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 11, 0),
                    Carbon::create(2021, 4, 1, 13, 0),
                    ['providerType' => DwsHomeHelpServiceProviderType::beginner()],
                ),
            ),

            // インターフェース仕様書 設定例 No.7【運転あり】
            // 運転には未対応のため未定義

            // インターフェース仕様書 設定例 No.8【空き時間あり】
            '6' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 5, 0),
                Carbon::create(2021, 4, 1, 10, 0),
                [],
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 5, 0),
                    Carbon::create(2021, 4, 1, 7, 0),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 30),
                    Carbon::create(2021, 4, 1, 10, 0),
                ),
            ),

            // インターフェース仕様書 設定例 No.9【空き時間複数あり】
            '7' => $this->makeChunkWithFragments(
                Carbon::create(2021, 4, 1, 5, 0),
                Carbon::create(2021, 4, 1, 10, 0),
                [],
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 5, 0),
                    Carbon::create(2021, 4, 1, 6, 15),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 6, 30),
                    Carbon::create(2021, 4, 1, 7, 30),
                ),
                $this->makeFragment(
                    Carbon::create(2021, 4, 1, 8, 45),
                    Carbon::create(2021, 4, 1, 10, 0),
                ),
            ),

            // インターフェース仕様書 設定例 No.10【運転あり空き時間あり】
            // 運転には未対応のため未定義

            // インターフェース仕様書 設定例 No.11【0時跨がり】
            '8' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 22, 0),
                Carbon::create(2021, 4, 2, 2, 0),
            ),

            // インターフェース仕様書 設定例 No.12【月跨がり（0時跨がり）】
            '9' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 30, 22, 0),
                Carbon::create(2021, 5, 1, 2, 0),
            ),

            // インターフェース仕様書 設定例 No.13【月跨がり（0時跨がり）】
            '10' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 50),
                Carbon::create(2021, 4, 2, 0, 50),
            ),

            // 石丸エラー
            '11' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 25),
                Carbon::create(2021, 4, 1, 20, 55),
            ),

            // 家事援助・最小単位（15分）で時間帯跨がり・前半50%超
            '12' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 20),
                Carbon::create(2021, 4, 1, 18, 20),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 家事援助・最小単位（15分）で時間帯跨がり・前半50%未満
            '13' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 25),
                Carbon::create(2021, 4, 1, 18, 25),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 家事援助・最小単位（最初の30分）で時間帯跨がり・前半50%超
            '14' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 45),
                Carbon::create(2021, 4, 1, 22, 30),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 家事援助・最小単位（最初の30分）で時間帯跨がり・前半50%未満
            '15' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 50),
                Carbon::create(2021, 4, 1, 22, 35),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%超
            '16' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 40),
                Carbon::create(2021, 4, 1, 18, 40),
                ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
            ),

            // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%
            '17' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 45),
                Carbon::create(2021, 4, 1, 18, 45),
                ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
            ),

            // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%未満
            '18' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 50),
                Carbon::create(2021, 4, 1, 18, 50),
                ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
            ),

            // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%超
            '19' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 40),
                Carbon::create(2021, 4, 1, 18, 40),
                ['category' => DwsServiceCodeCategory::accompany()],
            ),

            // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%
            '20' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 45),
                Carbon::create(2021, 4, 1, 18, 45),
                ['category' => DwsServiceCodeCategory::accompany()],
            ),

            // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%未満
            '21' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 50),
                Carbon::create(2021, 4, 1, 18, 50),
                ['category' => DwsServiceCodeCategory::accompany()],
            ),

            // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例8
            '22' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 7, 15),
                Carbon::create(2021, 4, 1, 9, 45),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例9
            '23' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 16, 0),
                Carbon::create(2021, 4, 1, 19, 0),
                ['category' => DwsServiceCodeCategory::housework()],
            ),

            // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例10
            '24' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 17, 0),
                Carbon::create(2021, 4, 1, 19, 0),
                ['category' => DwsServiceCodeCategory::housework()],
            ),
            // 重研 時間帯跨ぎ（深1.0・早2.0）
            '25' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 15),
                Carbon::create(2021, 4, 1, 8, 15),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 時間帯跨ぎ（深1.0・早1.5・日0.5）
            '26' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 30),
                Carbon::create(2021, 4, 1, 8, 30),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 時間帯跨ぎ（早2.5・日0.5）
            '27' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 5, 45),
                Carbon::create(2021, 4, 1, 8, 45),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 時間帯跨ぎ（早2.0・日1.0）
            '28' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 6, 00),
                Carbon::create(2021, 4, 1, 9, 00),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 時間帯跨ぎ（日1.0）
            '29' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 8, 00),
                Carbon::create(2021, 4, 1, 8, 30),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 時間帯跨ぎ（深1.0・深2.0）
            '30' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 15),
                Carbon::create(2021, 4, 2, 2, 15),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 日跨ぎ（深1.0・深2.0）
            '31' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 30),
                Carbon::create(2021, 4, 2, 2, 30),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 日跨ぎ（深1.0・深2.0）
            '32' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 45),
                Carbon::create(2021, 4, 2, 2, 45),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 家事援助・最小単位（最初の30分）で日跨がり・前半50%以上
            '33' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 23, 45),
                Carbon::create(2021, 4, 2, 0, 30),
                ['category' => DwsServiceCodeCategory::housework()],
            ),
            // 重研 日跨ぎ（夜1.0・深1.5・深2.5）
            '34' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 21, 30),
                Carbon::create(2021, 4, 2, 2, 30),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 日跨ぎ（夜2.0・深1.0）
            '35' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 22, 15),
                Carbon::create(2021, 4, 2, 1, 15),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 日跨ぎ（夜1.5・深1.5）
            '36' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 22, 30),
                Carbon::create(2021, 4, 2, 1, 30),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
            // 重研 日跨ぎ（夜1.5・深1.5）
            '37' => $this->makeChunkWithRange(
                Carbon::create(2021, 4, 1, 22, 45),
                Carbon::create(2021, 4, 2, 1, 45),
                ['category' => DwsServiceCodeCategory::physicalCare()],
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
            ),
        ];
    }
}
