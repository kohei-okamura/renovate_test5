<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\Common\TimeRange;
use Domain\FinderResult;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsvRow;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Lib\Csv;
use Lib\Exceptions\SetupException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsServiceDetailListInteractor;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingLocationAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingLocationAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingUserLocationAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingUserLocationAdditionUseCase;
use UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor;
use UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase;

/**
 * {@link \UseCase\Billing\BuildLtcsServiceDetailListInteractor} のテスト.
 */
final class BuildLtcsServiceDetailListInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use IdentifyUserLtcsCalcSpecUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Carbon $carbonExample;
    private CarbonRange $carbonRangeExample;
    private Seq $users;

    private BuildLtcsServiceDetailListInteractor $interactor;

    private FinderResult $dictionaryResult;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (BuildLtcsServiceDetailListInteractorTest $self): void {
            $self->carbonExample = Carbon::create(2008, 5, 17);
            $self->carbonRangeExample = CarbonRange::create([
                'start' => Carbon::create(1993, 6, 21),
                'end' => Carbon::create(1996, 6, 11),
            ]);
            $self->dictionaryResult = $self->dictionaryEntries();
            // 今回のテストでは以下のユースケースに処理を切り出すという対応をしたためテストでは以前のものと変更がないことが確認したい。
            // Mockを使うとMockの戻り値によってしまうため定義の手間がかかる & 以前のものと変わっていてもわからないという問題がある。
            // そのため今回のテストでは共通化して切り出したユースケースは実際の実装を使うようにする。
            $dependencies = [
                ComputeLtcsBillingEmergencyAdditionUseCase::class => ComputeLtcsBillingEmergencyAdditionInteractor::class,
                ComputeLtcsBillingFirstTimeAdditionUseCase::class => ComputeLtcsBillingFirstTimeAdditionInteractor::class,
                ComputeLtcsBillingLocationAdditionUseCase::class => ComputeLtcsBillingLocationAdditionInteractor::class,
                ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase::class => ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor::class,
                ComputeLtcsBillingTreatmentImprovementAdditionUseCase::class => ComputeLtcsBillingTreatmentImprovementAdditionInteractor::class,
                ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase::class => ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor::class,
                ComputeLtcsBillingUserLocationAdditionUseCase::class => ComputeLtcsBillingUserLocationAdditionInteractor::class,
                ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase::class => ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor::class,
            ];
            foreach ($dependencies as $abstract => $concrete) {
                app()->singleton($abstract, $concrete);
            }
        });
        self::beforeEachSpec(function (BuildLtcsServiceDetailListInteractorTest $self): void {
            $self->users = Seq::from($self->examples->users[0]);

            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn($self->dictionaryResult)
                ->byDefault();

            $self->computeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingServiceDetails[0]))
                ->byDefault();

            $self->identifyUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();

            $self->interactor = app(BuildLtcsServiceDetailListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find directory entries using given parameters', function (): void {
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with(
                    ['providedIn' => $this->carbonExample],
                    ['all' => true, 'sortBy' => 'id'],
                )
                ->andReturn($this->dictionaryEntries());

            $this->interactor->handle($this->context, $this->carbonExample, $this->provisionReports(), $this->users);
        });
        $this->should('throw NotFoundException when a report contains unexpected service code', function (): void {
            $this->assertThrows(SetupException::class, function (): void {
                $reportEntry = $this->provisionReportEntry(['serviceCode' => ServiceCode::fromString('119999')]);
                $report = $this->provisionReport(['entries' => [$reportEntry]]);
                $reports = $this->provisionReports([$report]);
                $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);
            });
        });
        $this->should('return a Seq of LtcsBillingServiceDetail', function (): void {
            $actual = $this->interactor->handle($this->context, $this->carbonExample, $this->provisionReports(), $this->users);

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should(
            'return a Seq of LtcsBillingServiceDetail contains addition/subtraction when the report contains it',
            function (ServiceOption $option): void {
                $reportEntry = $this->provisionReportEntry(['options' => [$option]]);
                $report = $this->provisionReport(['entries' => [$reportEntry]]);
                $reports = $this->provisionReports([$report]);

                $actual = $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);

                $this->assertMatchesModelSnapshot($actual);
            },
            [
                'examples' => [
                    'building subtraction 1' => [ServiceOption::over20()],
                    'building subtraction 2' => [ServiceOption::over50()],
                    'emergency addition' => [ServiceOption::emergency()],
                    'first time addition' => [ServiceOption::firstTime()],
                    'vital functions improvement 1' => [ServiceOption::vitalFunctionsImprovement1()],
                    'vital functions improvement 2' => [ServiceOption::vitalFunctionsImprovement2()],
                ],
            ]
        );
        $this->should(
            'return a Seq of LtcsBillingServiceDetail contains addition when the report contains it',
            function (array $reportAttr): void {
                $report = $this->provisionReport($reportAttr);
                $reports = $this->provisionReports([$report]);

                $actual = $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);

                $this->assertMatchesModelSnapshot($actual);
            },
            [
                'examples' => [
                    'specified area addition' => [
                        ['locationAddition' => LtcsOfficeLocationAddition::specifiedArea()],
                    ],
                    'small office addition' => [
                        ['locationAddition' => LtcsOfficeLocationAddition::mountainousArea()],
                    ],
                    'treatment improvement addition 1' => [
                        ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1()],
                    ],
                    'treatment improvement addition 2' => [
                        ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition2()],
                    ],
                    'treatment improvement addition 3' => [
                        ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition3()],
                    ],
                    'treatment improvement addition 4' => [
                        ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition4()],
                    ],
                    'treatment improvement addition 5' => [
                        ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition5()],
                    ],
                    'specified treatment improvement addition 1' => [
                        ['specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1()],
                    ],
                    'specified treatment improvement addition 2' => [
                        ['specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition2()],
                    ],
                    'base increase support addition' => [
                        ['baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1()],
                    ],
                ],
            ]
        );
        $this->should(
            'return a Seq of LtcsBillingServiceDetail contains multiple additions/subtractions when the report contains them',
            function (): void {
                $reportEntry = $this->provisionReportEntry([
                    'options' => [
                        ServiceOption::over20(),
                        ServiceOption::over50(),
                        ServiceOption::emergency(),
                        ServiceOption::firstTime(),
                        ServiceOption::vitalFunctionsImprovement1(),
                        ServiceOption::vitalFunctionsImprovement2(),
                    ],
                ]);
                $report = $this->provisionReport([
                    'locationAddition' => LtcsOfficeLocationAddition::specifiedArea(),
                    'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                    'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                    'entries' => [$reportEntry],
                ]);
                $reports = $this->provisionReports([$report]);

                $actual = $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);

                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Seq of LtcsBillingServiceDetail excluding the OwnExpenseProgram when the report contains it',
            function (): void {
                $reports = $this->provisionReports([
                    $this->provisionReport([
                        'entries' => [
                            $this->provisionReportEntry(),
                            $this->provisionReportEntry([
                                'ownExpenseProgramId' => 1,
                                'category' => LtcsProjectServiceCategory::ownExpense(),
                                'amounts' => [
                                    LtcsProjectAmount::create([
                                        'category' => LtcsProjectAmountCategory::ownExpense(),
                                        'amount' => 120,
                                    ]),
                                ],
                                'serviceCode' => null,
                            ]),
                        ],
                    ]),
                ]);

                $expected = $this->interactor->handle($this->context, $this->carbonExample, $this->provisionReports(), $this->users);
                $actual = $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);

                $this->assertSame(count($expected), count($actual));
                $this->assertEach(function ($e, $a) {
                    $this->assertModelStrictEquals($e, $a);
                }, $expected, $actual);
            }
        );
        $this->should('compute covid19 pandemic special addition using use case', function (): void {
            $reports = $this->provisionReports();
            $this->computeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase
                ->expects('handle')
                ->with(
                    $reports->head(),
                    $this->dictionaryResult->list,
                    typeOf('integer')
                )
                ->andReturn(Seq::from($this->examples->ltcsBillingServiceDetails[0]));

            $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users);
        });
        $this->should('return empty when main is empty', function (): void {
            $reports = $this->provisionReports()
                ->map(fn (LtcsProvisionReport $x): LtcsProvisionReport => $x->copy(['entries' => []]));

            $this->assertEmpty(
                $this->interactor->handle($this->context, $this->carbonExample, $reports, $this->users)
            );
        });
        $this->should('return a Seq of LtcsBillingServiceDetail for usePlan', function (): void {
            $actual = $this->interactor->handle($this->context, $this->carbonExample, $this->provisionReports(), $this->users, true);

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('use IdentifyUserLtcsCalcSpecUseCase', function (): void {
            $this->identifyUserLtcsCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], Mockery::capture($actual))
                ->andReturn(Option::none());

            $this->interactor->handle($this->context, $this->carbonExample, Seq::from($this->provisionReport()), $this->users, true);
            $this->assertTrue($this->carbonExample->lastOfMonth()->eq($actual));
        });
    }

    /**
     * テスト用の Carbon 一覧を生成する.
     *
     * @param int $year
     * @param int $month
     * @param null|iterable $dayOfMonths
     * @return array
     */
    private function dates(int $year = 2021, int $month = 1, ?iterable $dayOfMonths = null): array
    {
        $xs = $dayOfMonths ?? range(1, Carbon::create($year, $month, 1)->daysInMonth);
        return array_map(fn (int $d): Carbon => Carbon::create($year, $month, $d), [...$xs]);
    }

    /**
     * テスト用の介護保険サービス：予実：サービス情報を生成する.
     *
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReportEntry
     */
    private function provisionReportEntry(array $values = []): LtcsProvisionReportEntry
    {
        $attrs = [
            'slot' => TimeRange::create(['start' => '10:00', 'end' => '12:00']),
            'timeframe' => Timeframe::daytime(),
            'category' => LtcsProjectServiceCategory::physicalCare(),
            'amounts' => [
                LtcsProjectAmount::create([
                    'category' => LtcsProjectAmountCategory::physicalCare(),
                    'amount' => 120,
                ]),
            ],
            'headcount' => 1,
            'ownExpenseProgramId' => null,
            'serviceCode' => ServiceCode::fromString('111411'),
            'options' => [],
            'note' => '',
            'plans' => $this->dates(2021, 1, [6, 8, 13, 20, 27]),
            'results' => $this->dates(2021, 1, [7, 14, 21, 28]),
        ];
        return LtcsProvisionReportEntry::create($values + $attrs);
    }

    /**
     * テスト用の介護保険サービス：予実を生成する.
     *
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function provisionReport(array $values = []): LtcsProvisionReport
    {
        $attrs = [
            'id' => 1,
            'userId' => 1,
            'officeId' => 1,
            'contractId' => 1,
            'providedIn' => Carbon::create(2021, 1, 1),
            'entries' => [$this->provisionReportEntry()],
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return LtcsProvisionReport::create($values + $attrs);
    }

    /**
     * テスト用の介護保険サービス：予実一覧を生成する.
     *
     * @param null|array $xs
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Seq
     */
    private function provisionReports(?array $xs = null): Seq
    {
        $list = $xs
            ?? [
                $this->provisionReport(),
            ];
        return Seq::from(...$list);
    }

    /**
     * テスト用の介護保険サービス：訪問介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\FinderResult
     */
    private function dictionaryEntries(): FinderResult
    {
        $id = 1;
        $csv = codecept_data_dir('ServiceCodeDictionary/ltcs-home-visit-long-term-care-dictionary-csv-example.csv');
        $list = Seq::from(...Csv::read($csv))
            ->map(function (array $row) use (&$id): LtcsHomeVisitLongTermCareDictionaryEntry {
                return LtcsHomeVisitLongTermCareDictionaryCsvRow::create($row)->toDictionaryEntry(['id' => $id++]);
            });
        $pagination = Pagination::create([]);
        return FinderResult::from($list, $pagination);
    }
}
