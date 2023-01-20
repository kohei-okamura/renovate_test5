<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceChunkFinderMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceChunkRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsHomeHelpServiceChunkListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsHomeHelpServiceChunkListInteractor} Test.
 */
final class CreateDwsHomeHelpServiceChunkListInteractorTest extends Test
{
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use DwsHomeHelpServiceChunkFinderMixin;
    use DwsHomeHelpServiceChunkRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $repository;
    private CreateDwsHomeHelpServiceChunkListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->repository = [];

            $self->dwsHomeHelpServiceChunkFinder
                ->allows('find')
                ->andReturnUsing(function (array $filterParams) use ($self): FinderResult {
                    $seq = Seq::from(...$self->repository);
                    $list = isset($filterParams['category'])
                        ? $seq->find(function (DwsHomeHelpServiceChunk $x) use ($filterParams): bool {
                            return $x->category === $filterParams['category']
                                && $x->buildingType === $filterParams['buildingType']
                                && $x->isEmergency === false
                                && $x->isPlannedByNovice === $filterParams['isPlannedByNovice']
                                && $x->range->start < $filterParams['rangeStartBefore']
                                && $x->range->end > $filterParams['rangeEndAfter'];
                        })
                        : $seq;
                    return FinderResult::from($list->toSeq(), Pagination::create([]));
                })
                ->byDefault();

            $self->dwsHomeHelpServiceChunkRepository
                ->allows('store')
                ->andReturnUsing(function (DwsHomeHelpServiceChunk $x) use ($self): DwsHomeHelpServiceChunk {
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

            $self->interactor = app(CreateDwsHomeHelpServiceChunkListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle1(): void
    {
        $this->should(
            'return expected chunks for result',
            function (DwsProvisionReport $report): void {
                $actual = $this->interactor->handle($this->context, $this->dwsCertification, $report, Option::none(), false);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples('results')]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle2(): void
    {
        $this->should(
            'return expected chunks for plan',
            function (DwsProvisionReport $report): void {
                $actual = $this->interactor->handle($this->context, $this->dwsCertification, $report, Option::none(), true);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples('plans')]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle3(): void
    {
        $this->specify(
            '前日分の実績が存在するときに正しい DwsHomeHelpServiceChunk を生成する ',
            function (DwsProvisionReport $report, Option $previousReport): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    $this->dwsCertification,
                    $report,
                    $previousReport,
                    false
                );
                $this->assertMatchesModelSnapshot($actual);
            },
            [
                'examples' => [
                    '1' => [
                        $this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 2, 1, 0, 0),
                                Carbon::create(2021, 2, 1, 1, 0),
                            )],
                        ]),
                        Option::some($this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 1, 31, 20, 45),
                                Carbon::create(2021, 1, 31, 22, 15),
                            )],
                        ])),
                    ],
                    '2' => [
                        $this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 2, 1, 2, 45),
                                Carbon::create(2021, 2, 1, 4, 15),
                            )],
                        ]),
                        Option::some($this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 1, 31, 23, 30),
                                Carbon::create(2021, 2, 1, 1, 0),
                            )],
                        ])),
                    ],
                    '3' => [
                        $this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 2, 1, 3, 0),
                                Carbon::create(2021, 2, 1, 4, 30),
                            )],
                        ]),
                        Option::some($this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 1, 31, 23, 0),
                                Carbon::create(2021, 2, 1, 1, 0),
                            )],
                        ])),
                    ],
                    '4' => [
                        $this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 2, 1, 0, 0),
                                Carbon::create(2021, 2, 1, 1, 0),
                            )],
                        ]),
                        Option::some($this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 1, 30, 20, 45),
                                Carbon::create(2021, 1, 30, 22, 15),
                            )],
                        ])),
                    ],
                    '5' => [
                        $this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 2, 1, 2, 45),
                                Carbon::create(2021, 2, 1, 4, 15),
                            )],
                        ]),
                        Option::some($this->report([
                            'results' => [$this->provisionReportItem(
                                Carbon::create(2021, 1, 30, 23, 30),
                                Carbon::create(2021, 1, 31, 1, 0),
                            )],
                        ])),
                    ],
                ],
            ]
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
        // [FYI]
        // スナップショットの順序が変わると面倒なのでパターンを追加する場合は末尾に追加すること
        // 各パターンに英語の説明をつけるのが面倒なので日本語コメント + スナップショット番号としている
        return [
            // インターフェース仕様書 設定例 No.1【通常】
            '1' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 11, 30),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.2【ヘルパー要件あり】
            '2' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 11, 30),
                            ['options' => [ServiceOption::providedByCareWorkerForPwsd()]],
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.3【乗降の場合】
            // 通院等乗降介助には未対応のため未定義

            // インターフェース仕様書 設定例 No.4【同一時間2人派遣】
            // headcount = 2 ではなく別々に予実を登録した場合を想定
            '3' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 11, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 11, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.5【2人派遣時間ずれ】
            '4' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 12, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 11, 0),
                            Carbon::create(2021, 1, 23, 13, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.6【2人派遣ヘルパー要件違い】
            '5' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 10, 0),
                            Carbon::create(2021, 1, 23, 12, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 11, 0),
                            Carbon::create(2021, 1, 23, 13, 0),
                            ['options' => [ServiceOption::providedByBeginner()]],
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.7【運転あり】
            // 運転には未対応のため未定義

            // インターフェース仕様書 設定例 No.8【空き時間あり】
            '6' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 5, 0),
                            Carbon::create(2021, 1, 23, 7, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 30),
                            Carbon::create(2021, 1, 23, 10, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.9【空き時間あり複数】
            '7' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 5, 0),
                            Carbon::create(2021, 1, 23, 6, 15),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 6, 30),
                            Carbon::create(2021, 1, 23, 7, 30),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 8, 45),
                            Carbon::create(2021, 1, 23, 10, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.10【運転あり空き時間あり】
            // 運転には未対応のため未定義

            // インターフェース仕様書 設定例 No.11【0時跨がり】
            '8' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 22, 0),
                            Carbon::create(2021, 1, 24, 2, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.11【0時跨がり】改：2つに分けて実績を登録した場合
            '9' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 22, 0),
                            Carbon::create(2021, 1, 24, 0, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 24, 0, 0),
                            Carbon::create(2021, 1, 24, 2, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.12【月跨がり（0時跨がり）】
            // サービス単位生成の段階では月跨がりを考慮しない
            '10' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 22, 0),
                            Carbon::create(2021, 1, 24, 2, 0),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.13【月跨がり（0時跨がり）】
            // サービス単位生成の段階では月跨がりを考慮しない
            '11' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 1, 23, 23, 50),
                            Carbon::create(2021, 1, 24, 0, 50),
                        ),
                    ],
                ]),
            ],

            // インターフェース仕様書 設定例 No.5 + No.6
            '12' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2021, 4, 5, 10, 0),
                            Carbon::create(2021, 4, 5, 12, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 4, 5, 11, 0),
                            Carbon::create(2021, 4, 5, 13, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 4, 6, 10, 0),
                            Carbon::create(2021, 4, 6, 12, 0),
                        ),
                        $this->provisionReportItem(
                            Carbon::create(2021, 4, 6, 11, 0),
                            Carbon::create(2021, 4, 6, 13, 0),
                            ['options' => [ServiceOption::providedByBeginner()]],
                        ),
                    ],
                ]),
            ],
            // 日跨ぎ（重研の場合）
            '13' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2022, 1, 31, 23, 30),
                            Carbon::create(2022, 2, 1, 0, 30),
                            [
                                'options' => [ServiceOption::providedByCareWorkerForPwsd()],
                            ]
                        ),
                    ],
                ]),
            ],
            // 時間帯跨ぎ（重研の場合）
            '14' => [
                $this->report([
                    $key => [
                        $this->provisionReportItem(
                            Carbon::create(2022, 1, 23, 5, 15),
                            Carbon::create(2022, 1, 24, 8, 15),
                            [
                                'options' => [ServiceOption::providedByCareWorkerForPwsd()],
                            ]
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
            'category' => DwsProjectServiceCategory::physicalCare(),
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
