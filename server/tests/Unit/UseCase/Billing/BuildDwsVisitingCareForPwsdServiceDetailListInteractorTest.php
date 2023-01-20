<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DictionaryEntry;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Lib\Csv;
use Lib\Math;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Domain\Billing\DwsVisitingCareForPwsdChunkTestSupport;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdServiceDetailListInteractor} のテスト.
 */
final class BuildDwsVisitingCareForPwsdServiceDetailListInteractorTest extends Test
{
    use ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
    use CreateDwsVisitingCareForPwsdChunkListUseCaseMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport, DwsVisitingCareForPwsdChunkTestSupport {
        DwsBillingTestSupport::setupTestData as setupTestSupportData;
        DwsVisitingCareForPwsdChunkTestSupport::setupTestData insteadof DwsBillingTestSupport;
    }
    use DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private static array $entriesMap;

    private BuildDwsVisitingCareForPwsdServiceDetailListInteractor $interactor;

    /**
     * 初期化処理.
     *
     * @throws \Exception
     */
    public static function _setUpSuite(): void
    {
        self::$entriesMap = self::createEntriesMap();
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->setupTestSupportData();
            $self->report = $self->report->copy([
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::create(2021, 5, 1),
                            'start' => Carbon::create(2021, 5, 1, 0, 0, 0),
                            'end' => Carbon::create(2021, 5, 2, 0, 0, 0),
                        ]),
                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                        'headcount' => 1,
                        'options' => [],
                        'note' => '',
                    ]),
                ],
            ]);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->createDwsVisitingCareForPwsdChunkListUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('find')
                ->andReturnUsing(Closure::fromCallable([$self, 'findEntry']))
                ->byDefault();

            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('findByCategory')
                ->andReturnUsing(Closure::fromCallable([$self, 'findEntryByCategory']))
                ->byDefault();

            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('findByCategoryOption')
                ->andReturnUsing(Closure::fromCallable([$self, 'findEntryByCategoryOption']))
                ->byDefault();

            $self->computeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionUseCase
                ->allows('handle')
                ->andReturnUsing(Closure::fromCallable([$self, 'computeCovid19Addition']))
                ->byDefault();

            $self->interactor = app(BuildDwsVisitingCareForPwsdServiceDetailListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */

    /**
     * @test
     * @return void
     */
    public function describe_handle_main(): void
    {
        $this->should(
            'return a Seq of DwsBillingServiceDetail',
            function (Chunk ...$chunks): void {
                $chunkSeq = Seq::from(...$chunks);
                $this->createDwsVisitingCareForPwsdChunkListUseCase
                    ->expects('handle')
                    ->andReturn($chunkSeq);
                $providedIn = $chunkSeq
                    ->map(fn (Chunk $x): Carbon => $x->range->start)
                    ->min()
                    ->startOfMonth();

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->providedIn,
                    Option::none(),
                    Option::none(),
                    $this->dwsCertification,
                    $this->report->copy(['providedIn' => $providedIn]),
                );

                $this->assertInstanceOf(Seq::class, $actual);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples()]
        );
        $this->should('return a Seq of DwsBillingServiceDetail with specialAreaAddition', function () {
            $chunk = Seq::fromArray([
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 6, 12, 0, 0),
                    Carbon::create(2021, 6, 12, 1, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 6, 12, 0, 0),
                        Carbon::create(2021, 6, 12, 1, 0),
                    ),
                ),
            ]);
            $userCalcSpec = new UserDwsCalcSpec(
                id: null,
                userId: 1,
                effectivatedOn: Carbon::parse('2021-06-01'),
                locationAddition: DwsUserLocationAddition::specifiedArea(),
                isEnabled: true,
                version: 1,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now()
            );
            $providedIn = $chunk
                ->map(fn (Chunk $x): Carbon => $x->range->start)
                ->min()
                ->startOfMonth();
            $this->createDwsVisitingCareForPwsdChunkListUseCase
                ->expects('handle')
                ->andReturn($chunk);
            $actual = $this->interactor->handle(
                $this->context,
                $this->providedIn,
                Option::none(),
                Option::from($userCalcSpec),
                $this->dwsCertification,
                $this->report->copy(['providedIn' => $providedIn]),
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用のデータを生成する.
     */
    private function examples(): array
    {
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // インターフェース仕様書サービス提供実績記録票設定例
            '1' => [
                // インターフェース仕様書サービス提供実績記録票設定例 No.1
                // 1日に複数回提供
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 1, 4, 0),
                    Carbon::create(2021, 5, 1, 15, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 1, 4, 0),
                        Carbon::create(2021, 5, 1, 7, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 1, 8, 0),
                        Carbon::create(2021, 5, 1, 11, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 1, 12, 0),
                        Carbon::create(2021, 5, 1, 15, 0),
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.2
                // 移動あり
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 2, 4, 0),
                    Carbon::create(2021, 5, 2, 16, 30),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 4, 0),
                        Carbon::create(2021, 5, 2, 7, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 4, 0),
                        Carbon::create(2021, 5, 2, 7, 0),
                        ['isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 7, 30),
                        Carbon::create(2021, 5, 2, 11, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 13, 0),
                        Carbon::create(2021, 5, 2, 16, 30),
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.3
                // 移動4時間以上
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 3, 4, 0),
                    Carbon::create(2021, 5, 3, 17, 30),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 4, 0),
                        Carbon::create(2021, 5, 3, 7, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 4, 0),
                        Carbon::create(2021, 5, 3, 7, 30),
                        ['isMoving' => true, 'movingDurationMinutes' => 210],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 9, 0),
                        Carbon::create(2021, 5, 3, 12, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 9, 0),
                        Carbon::create(2021, 5, 3, 12, 0),
                        ['isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 14, 0),
                        Carbon::create(2021, 5, 3, 17, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 3, 14, 0),
                        Carbon::create(2021, 5, 3, 17, 30),
                        ['isMoving' => true, 'movingDurationMinutes' => 210],
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.4
                // 2人派遣同一時間
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 4, 4, 0),
                    Carbon::create(2021, 5, 4, 17, 30),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 4, 0),
                        Carbon::create(2021, 5, 4, 7, 30),
                        ['headcount' => 2],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 9, 0),
                        Carbon::create(2021, 5, 4, 12, 0),
                        ['headcount' => 2],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 14, 0),
                        Carbon::create(2021, 5, 4, 17, 30),
                        ['headcount' => 2],
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.5
                // 2人派遣時間ずれ
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 5, 4, 0),
                    Carbon::create(2021, 5, 5, 12, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 4, 0),
                        Carbon::create(2021, 5, 5, 12, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 4, 0),
                        Carbon::create(2021, 5, 5, 12, 0),
                        ['isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 6, 0),
                        Carbon::create(2021, 5, 5, 9, 0),
                        ['isSecondary' => true],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 6, 0),
                        Carbon::create(2021, 5, 5, 9, 0),
                        ['isSecondary' => true, 'isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.6
                // 13時間以上の提供で、かつ0時またがり
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 6, 4, 0),
                    Carbon::create(2021, 5, 7, 0, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 6, 4, 0),
                        Carbon::create(2021, 5, 7, 0, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 6, 4, 0),
                        Carbon::create(2021, 5, 7, 0, 0),
                        ['isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 7, 0, 0),
                    Carbon::create(2021, 5, 7, 1, 0),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.7
                // 13時間以上の提供で、かつ0時またがり二人派遣
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 8, 4, 0),
                    Carbon::create(2021, 5, 9, 0, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 8, 4, 0),
                        Carbon::create(2021, 5, 9, 0, 0),
                        ['headcount' => 2],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 8, 4, 0),
                        Carbon::create(2021, 5, 9, 0, 0),
                        ['headcount' => 2, 'isMoving' => true, 'movingDurationMinutes' => 180],
                    ),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 9, 0, 0),
                    Carbon::create(2021, 5, 9, 1, 0),
                    ['headcount' => 2],
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.8
                // 最小単位（30分）で0時またがり
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 10, 21, 45),
                    Carbon::create(2021, 5, 11, 0, 15),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 11, 0, 15),
                    Carbon::create(2021, 5, 11, 2, 45),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.9
                // 0時またがり複数サービス
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 12, 21, 0),
                    Carbon::create(2021, 5, 13, 0, 0),
                ),
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 13, 0, 0),
                    Carbon::create(2021, 5, 13, 5, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 13, 0, 0),
                        Carbon::create(2021, 5, 13, 0, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 13, 1, 30),
                        Carbon::create(2021, 5, 13, 5, 0),
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.10
                // 0時またがりサービス終了
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 14, 23, 0),
                    Carbon::create(2021, 5, 15, 0, 0),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.11
                // 二人派遣（移動介護）でサービス時間がずれた場合
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 15, 6, 0),
                    Carbon::create(2021, 5, 15, 12, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 15, 6, 0),
                        Carbon::create(2021, 5, 15, 12, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 15, 6, 0),
                        Carbon::create(2021, 5, 15, 12, 0),
                        ['isMoving' => true, 'movingDurationMinutes' => 360],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 15, 8, 0),
                        Carbon::create(2021, 5, 15, 10, 0),
                        ['isSecondary' => true],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 15, 8, 0),
                        Carbon::create(2021, 5, 15, 10, 0),
                        ['isSecondary' => true, 'isMoving' => true, 'movingDurationMinutes' => 120],
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.12
                // 最初の1時間で0時またがり
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 16, 23, 45),
                    Carbon::create(2021, 5, 17, 0, 45),
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 17, 0, 45),
                    Carbon::create(2021, 5, 17, 2, 45),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.13
                // 入院中にサービス提供を行った場合
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 18, 6, 0),
                    Carbon::create(2021, 5, 18, 12, 0),
                    ['isHospitalized' => true],
                ),
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 19, 6, 0),
                    Carbon::create(2021, 5, 19, 12, 0),
                    ['isLongHospitalized' => true],
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.14
                // 二人派遣（熟練ヘルパーが同一時間帯に新任ヘルパーに同行した場合）
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 5, 20, 8, 0),
                    Carbon::create(2021, 5, 20, 12, 0),
                    ['isCoaching' => true, 'headcount' => 2],
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.15
                // 二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 21, 8, 0),
                    Carbon::create(2021, 5, 21, 16, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 21, 8, 0),
                        Carbon::create(2021, 5, 21, 12, 0),
                        ['isCoaching' => true, 'headcount' => 2],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 21, 14, 0),
                        Carbon::create(2021, 5, 21, 16, 0),
                        ['headcount' => 2],
                    ),
                ),

                // インターフェース仕様書サービス提供実績記録票設定例 No.16
                // 二人派遣（熟練ヘルパーが一部の時間帯に新任ヘルパーに同行した場合）
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 22, 8, 0),
                    Carbon::create(2021, 5, 22, 16, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 22, 8, 0),
                        Carbon::create(2021, 5, 22, 12, 0),
                        ['isCoaching' => true, 'headcount' => 2],
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 22, 12, 0),
                        Carbon::create(2021, 5, 22, 16, 0),
                    ),
                ),
            ],

            // 自立支援給付（居宅介護・重度訪問介護）の請求に関する留意事項について（世田谷区・平成30年）
            // https://www.city.setagaya.lg.jp/mokuji/fukushi/002/017/d00158485_d/fil/ziritsu.pdf
            '2' => [
                // 例1. 重訪Ⅱの者に対して、重度訪問介護を13時〜19時まで行った場合
                ...Seq::fromArray($this->makeChunkWithRange(
                    Carbon::create(2021, 5, 1, 13, 0),
                    Carbon::create(2021, 5, 1, 19, 0),
                ))->map(
                    fn (Chunk $x): Chunk => $x->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd2()])
                ),

                // 例2. 重訪Ⅲの者に対して、重度訪問介護を 7:00〜9:00 の2時間と、22:45〜翌0:45 の2時間の計4時間を行った場合
                ...Seq::fromArray($this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 2, 7, 0),
                    Carbon::create(2021, 5, 3, 0, 15),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 7, 0),
                        Carbon::create(2021, 5, 2, 9, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 2, 22, 45),
                        Carbon::create(2021, 5, 3, 0, 15),
                    ),
                ))->map(
                    fn (Chunk $x): Chunk => $x->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd3()])
                ),
                ...Seq::fromArray($this->makeChunkWithRange(
                    Carbon::create(2021, 5, 3, 0, 15),
                    Carbon::create(2021, 5, 3, 0, 45),
                ))->map(
                    fn (Chunk $x): Chunk => $x->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd3()])
                ),

                // 例3. 重訪Ⅲの者に対して、重度訪問介護を 8:00〜9:30 の1時間半、11:00〜12:30 の1時間半、
                //      16:00〜19:20 の3時間20分、計6時間20分行った場合
                ...Seq::fromArray($this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 4, 8, 0),
                    Carbon::create(2021, 5, 4, 19, 20),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 8, 0),
                        Carbon::create(2021, 5, 4, 9, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 11, 0),
                        Carbon::create(2021, 5, 4, 12, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 4, 16, 0),
                        Carbon::create(2021, 5, 4, 19, 20),
                    ),
                ))->map(
                    fn (Chunk $x): Chunk => $x->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd3()])
                ),

                // 例4. 重訪Ⅲの者に対して、重度訪問介護を 7:30〜11:00 の3時間半、15:50〜18:20 までの2時間半行った場合
                ...Seq::fromArray($this->makeChunkWithFragments(
                    Carbon::create(2021, 5, 5, 7, 30),
                    Carbon::create(2021, 5, 5, 18, 20),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 7, 30),
                        Carbon::create(2021, 5, 5, 11, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 5, 5, 15, 50),
                        Carbon::create(2021, 5, 5, 18, 20),
                    ),
                ))->map(
                    fn (Chunk $x): Chunk => $x->copy(['category' => DwsServiceCodeCategory::visitingCareForPwsd3()])
                ),
            ],

            // DEV-6438 基準時以下（15分等）の予実があった場合に算定がずれる場合がある
            // https://eustylelab.backlog.com/view/DEV-6438
            '3' => [
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 7, 1, 0, 0),
                    Carbon::create(2021, 7, 2, 0, 0),
                    $this->makeFragment(
                        Carbon::create(2021, 7, 1, 0, 0),
                        Carbon::create(2021, 7, 1, 8, 45),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 7, 1, 12, 0),
                        Carbon::create(2021, 7, 1, 17, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 7, 1, 21, 0),
                        Carbon::create(2021, 7, 2, 0, 0),
                    ),
                ),
            ],

            // DEV-6462 ミョウバヤシさんのケース（2人目が複数回現れる）
            '4' => [
                ...$this->makeChunkWithFragments(
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
            ],
            // 移動加算が75分のときに移動加算1.5が算定されるケース
            '5' => [
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 6, 12, 0, 0),
                    Carbon::create(2021, 6, 12, 1, 15),
                    $this->makeFragment(
                        Carbon::create(2021, 6, 12, 0, 0),
                        Carbon::create(2021, 6, 12, 1, 15),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 6, 12, 0, 0),
                        Carbon::create(2021, 6, 12, 1, 15),
                        ['isMoving' => true, 'movingDurationMinutes' => 75]
                    ),
                ),
            ],
        ];
    }

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder::find} のモック実装.
     *
     * @param array $filterParams
     * @return \Domain\FinderResult
     */
    private function findEntry(array $filterParams): FinderResult
    {
        $category = $filterParams['category'];
        $seq = self::$entriesMap[$category->value()];
        $list = $seq->filter(function (DictionaryEntry $x) use ($filterParams): bool {
            return $x->isCoaching === $filterParams['isCoaching']
                && $x->timeframe === $filterParams['timeframe']
                && (empty($filterParams['isHospitalized']) || $x->isHospitalized === $filterParams['isHospitalized'])
                && (empty($filterParams['isLongHospitalized']) || $x->isLongHospitalized === $filterParams['isLongHospitalized']);
        });
        return FinderResult::from($list, Pagination::create([]));
    }

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder::findByCategoryOption()} のモック実装.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|\ScalikePHP\Option
     * @noinspection PhpUnusedParameterInspection
     */
    private function findEntryByCategoryOption(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): Option {
        return self::$entriesMap[$category->value()]->headOption();
    }

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder::findByCategory()} のモック実装.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    private function findEntryByCategory(
        Carbon $providedIn,
        DwsServiceCodeCategory $category
    ): DwsVisitingCareForPwsdDictionaryEntry {
        return $this->findEntryByCategoryOption($providedIn, $category)->get();
    }

    /**
     * {@link \UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase} のモック実装.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore 加算対象の単位数
     * @param \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|\ScalikePHP\Option $dictionaryEntryOption
     * @return \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq
     * @noinspection PhpUnusedParameterInspection
     */
    private function computeCovid19Addition(
        Context $context,
        DwsProvisionReport $provisionReport,
        int $baseScore,
        Option $dictionaryEntryOption
    ): Seq {
        return $provisionReport->providedIn->between('2021-04-01', '2021-09-30')
            ? $dictionaryEntryOption
                ->toSeq()
                ->map(function (DictionaryEntry $entry) use ($provisionReport, $baseScore): DwsBillingServiceDetail {
                    $score = $baseScore < 500 ? 1 : Math::round($baseScore * 0.001);
                    return DwsBillingServiceDetail::create([
                        'userId' => $provisionReport->userId,
                        'providedOn' => $provisionReport->providedIn->endOfMonth(),
                        'serviceCode' => $entry->serviceCode,
                        'serviceCodeCategory' => $entry->category,
                        'unitScore' => $score,
                        'isAddition' => true,
                        'count' => 1,
                        'totalScore' => $score,
                    ]);
                })
            : Seq::empty();
    }

    /**
     * 指定された値 `$x` が start を超え `end` 以下であるかどうかを判定する.
     *
     * @param int $x
     * @param \Domain\Common\IntRange $range
     * @return bool
     */
    private static function between(int $x, IntRange $range): bool
    {
        return $x > $range->start && $x <= $range->end;
    }

    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @throws \Exception
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[][]|\ScalikePHP\Seq[]
     */
    private static function createEntriesMap(): array
    {
        return Seq::from(...self::readEntries())
            ->groupBy(fn (DictionaryEntry $x): int => $x->category->value())
            ->toAssoc();
    }

    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|iterable
     */
    private static function readEntries(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('Billing/dict-dws-12_R304.csv');
        $data = Csv::read($csv);
        foreach (DwsVisitingCareForPwsdDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }
}
