<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsLevel;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdChunkFinderMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdChunkRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor} のテスト.
 */
final class CreateDwsVisitingCareForPwsdChunkListInteractorTest extends Test
{
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use DwsVisitingCareForPwsdChunkFinderMixin;
    use DwsVisitingCareForPwsdChunkRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $repository;
    private DwsCertificationGrant $grant;

    private CreateDwsVisitingCareForPwsdChunkListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->grant = DwsCertificationGrant::create([
                'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                'grantedAmount' => '',
                'activatedOn' => Carbon::create(2020, 1, 1),
                'deactivatedOn' => Carbon::create(2022, 12, 31),
            ]);
            $self->dwsCertification = $self->dwsCertification->copy([
                'dwsLevel' => DwsLevel::level5(),
                'isSubjectOfComprehensiveSupport' => false,
                'activatedOn' => Carbon::create(2020, 1, 1),
                'deactivatedOn' => Carbon::create(2022, 12, 31),
                'grants' => [$self->grant],
            ]);
            $self->report = $self->report([
                'providedIn' => Carbon::create(2021, 1),
                'results' => [
                    $self->provisionReportItem(
                        Carbon::create(2021, 1, 23, 6, 0, 0),
                        Carbon::create(2021, 1, 23, 12, 0, 0),
                    ),
                ],
            ]);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->repository = [];

            $self->dwsVisitingCareForPwsdChunkFinder
                ->allows('find')
                ->andReturnUsing(function (array $filterParams) use ($self): FinderResult {
                    $seq = Seq::from(...$self->repository);
                    $list = isset($filterParams['category'])
                        ? $seq->find(function (DwsVisitingCareForPwsdChunk $x) use ($filterParams): bool {
                            return $x->category === $filterParams['category']
                                && $x->providedOn->eq($filterParams['providedOn']);
                        })
                        : $seq;
                    return FinderResult::from($list->toSeq(), Pagination::create([]));
                })
                ->byDefault();

            $self->dwsVisitingCareForPwsdChunkRepository
                ->allows('store')
                ->andReturnUsing(function (DwsVisitingCareForPwsdChunk $x) use ($self): DwsVisitingCareForPwsdChunk {
                    if ($x->id) {
                        $self->repository[$x->id] = $x;
                        return $x;
                    } else {
                        $id = max([0, ...array_keys($self->repository)]) + 1;
                        $entity = $x->copy(['id' => $id]);
                        $self->repository[$id] = $entity;
                        return $entity;
                    }
                })
                ->byDefault();

            $self->transactionManager
                ->allows('rollback')
                ->andReturnUsing(function (Closure $callback) use ($self) {
                    try {
                        return $callback();
                    } finally {
                        $self->repository = [];
                    }
                })
                ->byDefault();

            $self->interactor = app(CreateDwsVisitingCareForPwsdChunkListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle1(): void
    {
        $this->specify(
            '予実の「実績」に対して期待通りのサービス単位（重度訪問介護）が生成される',
            function (DwsProvisionReport $report): void {
                $actual = $this->interactor->handle(
                    context: $this->context,
                    certification: $this->dwsCertification,
                    provisionReport: $report,
                    isPlan: false
                );
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples('results')]
        );
        $this->specify(
            '受給者証のサービス種別が「重度訪問介護（重度障害者等包括支援対象者）」の場合はサービスコード区分が「重訪Ⅰ（重度障害者等の場合）」となる',
            function (): void {
                $certification = $this->dwsCertification->copy([
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => true,
                    'grants' => [
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1(),
                        ]),
                    ],
                ]);

                $chunks = $this->interactor->handle(
                    context: $this->context,
                    certification: $certification,
                    provisionReport: $this->report,
                    isPlan: false
                );

                $this->assertCount(1, $chunks);
                foreach ($chunks as $actual) {
                    $this->assertEquals(DwsServiceCodeCategory::visitingCareForPwsd1(), $actual->category);
                }
            },
        );
        $this->specify(
            '受給者証のサービス種別が「重度訪問介護（障害支援区分6該当者）」の場合はサービスコード区分が「重訪Ⅱ（障害支援区分6に該当する者の場合）」となる',
            function (): void {
                $certification = $this->dwsCertification->copy([
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => false,
                    'grants' => [
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                        ]),
                    ],
                ]);

                $chunks = $this->interactor->handle(
                    context: $this->context,
                    certification: $certification,
                    provisionReport: $this->report,
                    isPlan: false
                );

                $this->assertCount(1, $chunks);
                foreach ($chunks as $actual) {
                    $this->assertEquals(DwsServiceCodeCategory::visitingCareForPwsd2(), $actual->category);
                }
            },
        );
        $this->specify(
            '受給者証のサービス種別が「重度訪問介護（その他）」の場合はサービスコード区分が「重訪Ⅲ」となる',
            function (DwsLevel $dwsLevel): void {
                $certification = $this->dwsCertification->copy([
                    'dwsLevel' => $dwsLevel,
                    'isSubjectOfComprehensiveSupport' => false,
                    'grants' => [
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                        ]),
                    ],
                ]);

                $chunks = $this->interactor->handle(
                    context: $this->context,
                    certification: $certification,
                    provisionReport: $this->report,
                    isPlan: false
                );

                $this->assertCount(1, $chunks);
                foreach ($chunks as $actual) {
                    $this->assertEquals(DwsServiceCodeCategory::visitingCareForPwsd3(), $actual->category);
                }
            },
            [
                'examples' => [
                    '障害支援区分6' => [DwsLevel::level6()],
                    '障害支援区分5' => [DwsLevel::level5()],
                    '障害支援区分4' => [DwsLevel::level4()],
                    '障害支援区分3' => [DwsLevel::level3()],
                ],
            ],
        );
        $this->specify(
            '受給者証に「介護給付費の支給決定内容」が複数ある場合は予実の「サービス提供年月」に一致するサービス種別に応じてサービスコード区分が決定される',
            function (): void {
                $certification = $this->dwsCertification->copy([
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => false,
                    'grants' => [
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd3(),
                            'grantedAmount' => '',
                            'activatedOn' => Carbon::create(2019, 1, 1),
                            'deactivatedOn' => Carbon::create(2020, 12, 31),
                        ]),
                        $this->grant->copy([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                            'grantedAmount' => '',
                            'activatedOn' => Carbon::create(2021, 1, 1),
                            'deactivatedOn' => Carbon::create(2022, 12, 31),
                        ]),
                    ],
                ]);

                $chunks = $this->interactor->handle(
                    context: $this->context,
                    certification: $certification,
                    provisionReport: $this->report,
                    isPlan: false
                );

                $this->assertCount(1, $chunks);
                foreach ($chunks as $actual) {
                    $this->assertEquals(DwsServiceCodeCategory::visitingCareForPwsd2(), $actual->category);
                }
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle2(): void
    {
        $this->specify(
            '予実の「予定」に対して期待通りのサービス単位（重度訪問介護）が生成される',
            function (DwsProvisionReport $report): void {
                $actual = $this->interactor->handle(
                    context: $this->context,
                    certification: $this->dwsCertification,
                    provisionReport: $report,
                    isPlan: true
                );
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples('plans')]
        );
    }

    /**
     * テスト用に用いる値の一覧を生成する.
     *
     * @param string $key
     * @return array
     */
    private function examples(string $key): array
    {
        // FYI: スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        return [
            '1: インターフェース仕様書 設定例 No.1【1日に複数回提供】' => [
                // 4:00〜7:00 + 8:00〜11:00 + 12:00〜15:00
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 23, 7, 0, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 0, 0),
                            Carbon::create(2021, 1, 23, 11, 0, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            Carbon::create(2021, 1, 23, 15, 0, 0),
                        ),
                    ],
                ]),
            ],
            '2: インターフェース仕様書 設定例 No.2【移動あり】' => [
                // 4:00〜7:00（移動加算あり） + 7:30〜11:00 + 13:00〜16:30
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 23, 7, 0, 0),
                            ['movingDurationMinutes' => 180],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 7, 30, 0),
                            Carbon::create(2021, 1, 23, 11, 0, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 13, 0, 0),
                            Carbon::create(2021, 1, 23, 16, 30, 0),
                        ),
                    ],
                ]),
            ],
            '3: インターフェース仕様書 設定例 No.3【移動4時間以上】' => [
                // 4:00〜7:30（移動加算あり） + 9:00〜12:00（移動加算あり） + 14:00〜17:30（移動加算あり）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 23, 7, 30, 0),
                            ['movingDurationMinutes' => 210],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 9, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['movingDurationMinutes' => 180],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 14, 0, 0),
                            Carbon::create(2021, 1, 23, 17, 30, 0),
                            ['movingDurationMinutes' => 210],
                        ),
                    ],
                ]),
            ],
            '4: インターフェース仕様書 設定例 No.4【二人派遣同一時間】' => [
                // 4:00〜7:30（2人） + 9:00〜12:00（2人） + 14:00〜17:30（2人）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 23, 7, 30, 0),
                            ['headcount' => 2],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 9, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['headcount' => 2],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 14, 0, 0),
                            Carbon::create(2021, 1, 23, 17, 30, 0),
                            ['headcount' => 2],
                        ),
                    ],
                ]),
            ],
            '5: インターフェース仕様書 設定例 No.5【二人派遣時間ずれ】' => [
                // 4:00〜9:00（1人目） + 6:00〜12:00（2人目） + 6:00〜9:00（移動加算あり・2人）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 23, 9, 0, 0),
                            ['movingDurationMinutes' => 180],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 6, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['movingDurationMinutes' => 180],
                        ),
                    ],
                ]),
            ],
            '6: インターフェース仕様書 設定例 No.6【13時間以上の提供で、かつ0時またがり】' => [
                // 4:00〜翌1:00 + 移動介護180分
                // 日跨ぎ + 移動加算を1つのサービスとして登録することは想定しないため移動加算なしでテスト
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 24, 1, 0, 0),
                        ),
                    ],
                ]),
            ],
            '7: インターフェース仕様書 設定例 No.7【13時間以上の提供で、かつ0時またがり二人派遣】' => [
                // 4:00〜翌1:00 + 移動介護180分 × 2人
                // 日跨ぎ + 移動加算を1つのサービスとして登録することは想定しないため移動加算なしでテスト
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 4, 0, 0),
                            Carbon::create(2021, 1, 24, 1, 0, 0),
                            ['headcount' => 2],
                        ),
                    ],
                ]),
            ],
            '8: インターフェース仕様書 設定例 No.8【最小単位（30分）で0時またがり】' => [
                // 21:45〜翌2:45（1日目135分 + 2日目165分）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 21, 45, 0),
                            Carbon::create(2021, 1, 24, 2, 45, 0),
                        ),
                    ],
                ]),
            ],
            '9: インターフェース仕様書 設定例 No.9【0時またがり複数サービス】' => [
                // 21:00〜翌0:30 + 翌1:30〜5:00（1日目180分 + 240分）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 21, 0, 0),
                            Carbon::create(2021, 1, 24, 0, 30, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 24, 1, 30, 0),
                            Carbon::create(2021, 1, 24, 5, 0, 0),
                        ),
                    ],
                ]),
            ],
            '10: インターフェース仕様書 設定例 No.10【0時またがりサービス終了】' => [
                // 23:00〜翌0:30（1日目60分 + 2日目30分）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 23, 0, 0),
                            Carbon::create(2021, 1, 24, 0, 30, 0),
                        ),
                    ],
                ]),
            ],
            '11: インターフェース仕様書 設定例 No.11【二人派遣（移動介護）でサービス時間がずれた場合】' => [
                // 6:00〜10:00（1人目・移動加算あり） + 8:00〜12:00（2人目・移動加算あり）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 6, 0, 0),
                            Carbon::create(2021, 1, 23, 10, 0, 0),
                            ['movingDurationMinutes' => 240],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['movingDurationMinutes' => 240],
                        ),
                    ],
                ]),
            ],
            '12: インターフェース仕様書 設定例 No.12【最初の1時間で0時またがり】' => [
                // 23:45〜翌2:45
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 23, 45, 0),
                            Carbon::create(2021, 1, 24, 2, 45, 0),
                        ),
                    ],
                ]),
            ],
            '13: インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日以内' => [
                // 6:00〜12:00
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 6, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['options' => [ServiceOption::hospitalized()]],
                        ),
                    ],
                ]),
            ],
            '14: インターフェース仕様書 設定例 No.13【入院中にサービス提供を行った場合】※90日超' => [
                // 6:00〜12:00
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 6, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            ['options' => [ServiceOption::longHospitalized()]],
                        ),
                    ],
                ]),
            ],
            '15: インターフェース仕様書 設定例 No.14【二人派遣（熟練ヘルパーが同一時間帯に新任ヘルパーに同行した場合）】' => [
                // 8:00〜12:00（同行・2人）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            [
                                'headcount' => 2,
                                'options' => [ServiceOption::coaching()],
                            ],
                        ),
                    ],
                ]),
            ],
            '16: インターフェース仕様書 設定例 No.15【二人派遣（同一日に熟練ヘルパーと新任ヘルパーが混在した場合）】' => [
                // 8:00〜12:00（同行・2人） + 14:00〜16:00（2人）
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            [
                                'headcount' => 2,
                                'options' => [ServiceOption::coaching()],
                            ],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 14, 0, 0),
                            Carbon::create(2021, 1, 23, 16, 0, 0),
                            ['headcount' => 2],
                        ),
                    ],
                ]),
            ],
            '17: インターフェース仕様書 設定例 No.16【二人派遣（熟練ヘルパーが一部の時間帯に新任ヘルパーに同行した場合）】' => [
                // 8:00〜12:00（同行・2人） + 12:00〜16:00
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 0, 0),
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            [
                                'headcount' => 2,
                                'options' => [ServiceOption::coaching()],
                            ],
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 12, 0, 0),
                            Carbon::create(2021, 1, 23, 16, 0, 0),
                        ),
                    ],
                ]),
            ],
            '18: インターフェース仕様書 設定例 No.9【0時またがり複数サービス】2日目（改）DEV-5792' => [
                // See https://eustylelab.backlog.com/view/DEV-5792#comment-74826168
                // 21:45〜翌0:30 + 翌1:30〜5:00
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 21, 45, 0),
                            Carbon::create(2021, 1, 24, 0, 30, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 24, 1, 30, 0),
                            Carbon::create(2021, 1, 24, 5, 0, 0),
                        ),
                    ],
                ]),
            ],
        ];
    }

    /**
     * テスト用に用いる障害福祉サービス：予実：要素を生成する.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array $attrs
     * @return \Domain\ProvisionReport\DwsProvisionReportItem
     */
    private function provisionReportItem(Carbon $start, Carbon $end, array $attrs = []): DwsProvisionReportItem
    {
        $values = [
            'schedule' => Schedule::create([
                'date' => $start->startOfDay(),
                'start' => $start,
                'end' => $end,
            ]),
            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'results' => [],
            'plans' => [],
            'options' => [],
            'note' => '',
        ];
        return DwsProvisionReportItem::create($attrs + $values);
    }
}
