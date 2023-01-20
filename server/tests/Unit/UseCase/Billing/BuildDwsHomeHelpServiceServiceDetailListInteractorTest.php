<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as DictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Lib\Csv;
use Lib\Math;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Domain\Billing\DwsHomeHelpServiceChunkTestSupport;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsHomeHelpServiceChunkListUseCaseMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceDetailListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsHomeHelpServiceServiceDetailListInteractor} のテスト.
 */
final class BuildDwsHomeHelpServiceServiceDetailListInteractorTest extends Test
{
    use ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCaseMixin;
    use DummyContextMixin;
    use CreateDwsHomeHelpServiceChunkListUseCaseMixin;
    use DwsBillingTestSupport, DwsHomeHelpServiceChunkTestSupport {
        DwsBillingTestSupport::setupTestData as setupTestSupportData;
        DwsHomeHelpServiceChunkTestSupport::setupTestData insteadof DwsBillingTestSupport;
    }
    use DwsHomeHelpServiceDictionaryEntryFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private static array $entriesMap;

    private BuildDwsHomeHelpServiceServiceDetailListInteractor $interactor;

    /**
     * 初期化処理.
     *
     * @throws \Exception
     * @return void
     */
    public static function _setUpSuite(): void
    {
        self::$entriesMap = self::createEntriesMap();
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->setupTestSupportData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->createDwsHomeHelpServiceChunkListUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->dwsHomeHelpServiceDictionaryEntryFinder
                ->allows('find')
                ->andReturnUsing(Closure::fromCallable([$self, 'findEntry']))
                ->byDefault();

            $self->computeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase
                ->allows('handle')
                ->andReturnUsing(Closure::fromCallable([$self, 'computeCovid19Addition']))
                ->byDefault();

            $self->interactor = app(BuildDwsHomeHelpServiceServiceDetailListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle_main(): void
    {
        $this->should(
            'return a Seq of DwsBillingServiceDetail',
            function (DwsHomeHelpServiceChunk ...$chunks): void {
                $chunkSeq = Seq::from(...$chunks);
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->andReturn($chunkSeq);
                $providedIn = $chunkSeq
                    ->map(fn (DwsHomeHelpServiceChunk $x): Carbon => $x->range->start)
                    ->min()
                    ->startOfMonth();

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->providedIn,
                    Option::none(),
                    Option::none(),
                    $this->dwsCertification,
                    $this->report->copy(['providedIn' => $providedIn]),
                    Option::some($this->previousReports[0])
                );

                $this->assertInstanceOf(Seq::class, $actual);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples()]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle_special_addition(): void
    {
        $this->specify('特別地域加算のサービス詳細が含まれた Seq を返す', function () {
            $chunk = Seq::fromArray([
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 6, 12, 0, 0),
                    Carbon::create(2021, 6, 12, 1, 0),
                    [],
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
                ->map(fn (DwsHomeHelpServiceChunk $x): Carbon => $x->range->start)
                ->min()
                ->startOfMonth();
            $this->createDwsHomeHelpServiceChunkListUseCase
                ->expects('handle')
                ->andReturn($chunk);
            $actual = $this->interactor->handle(
                $this->context,
                $this->providedIn,
                Option::none(),
                Option::from($userCalcSpec),
                $this->dwsCertification,
                $this->report->copy(['providedIn' => $providedIn]),
                Option::some($this->previousReports[0])
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle_covid19PandemicSpecialAddition(): void
    {
        $examples = [
            'Mar. 2021' => [Carbon::create(2021, 3, 1), false],
            'Apr. 2021' => [Carbon::create(2021, 4, 1), true],
            'May. 2021' => [Carbon::create(2021, 5, 1), true],
            'Jun. 2021' => [Carbon::create(2021, 6, 1), true],
            'JUl. 2021' => [Carbon::create(2021, 7, 1), true],
            'Aug. 2021' => [Carbon::create(2021, 8, 1), true],
            'Sep. 2021' => [Carbon::create(2021, 9, 1), true],
            'Oct. 2021' => [Carbon::create(2021, 10, 1), false],
        ];
        $this->should(
            'return a Seq that includes COVID-19 addition between 2021-04-01 and 2021-09-30',
            function (Carbon $providedIn, bool $included): void {
                $chunks = $this->makeChunkWithRange(
                    $providedIn->hour(17)->minute(25),
                    $providedIn->hour(20)->minute(55),
                );
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(...$chunks));

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->providedIn,
                    Option::none(),
                    Option::none(),
                    $this->dwsCertification,
                    $this->report->copy(['providedIn' => $providedIn]),
                    Option::some($this->previousReports[0])
                );

                $this->assertSame(
                    $actual->exists(fn (DwsBillingServiceDetail $x): bool => $x->serviceCode->toString() === '11ZZ01'),
                    $included
                );
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle_specifiedOfficeAddition(): void
    {
        // テストデータ生成用に本体サービスコードの6番目（世田谷）を用いる
        $chunks = $this->examples()[6];
        $chunkSeq = Seq::from(...$chunks);
        $examples = [
            'none' => [HomeHelpServiceSpecifiedOfficeAddition::none()],
            'addition1' => [HomeHelpServiceSpecifiedOfficeAddition::addition1()],
            'addition2' => [HomeHelpServiceSpecifiedOfficeAddition::addition2()],
            'addition3' => [HomeHelpServiceSpecifiedOfficeAddition::addition3()],
            'addition4' => [HomeHelpServiceSpecifiedOfficeAddition::addition4()],
        ];
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
        $this->should(
            'return a Seq that includes specified office addition when the spec indicated',
            function (HomeHelpServiceSpecifiedOfficeAddition $specifiedOfficeAddition) use ($chunkSeq, $userCalcSpec): void {
                $providedIn = $chunkSeq
                    ->map(fn (DwsHomeHelpServiceChunk $x): Carbon => $x->range->start)
                    ->min()
                    ->startOfMonth();
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->andReturn($chunkSeq);

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->providedIn,
                    Option::from($this->homeHelpServiceCalcSpec->copy([
                        'specifiedOfficeAddition' => $specifiedOfficeAddition,
                        'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::none(),
                        'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::none(),
                    ])),
                    Option::from($userCalcSpec),
                    $this->dwsCertification,
                    $this->report->copy(['providedIn' => $providedIn]),
                    Option::none()
                );

                $this->assertMatchesModelSnapshot($actual);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle_additions(): void
    {
        $examples = [
            'emergency addition' => [
                ServiceOption::emergency(),
            ],
            'first time addition' => [
                ServiceOption::firstTime(),
            ],
            'sucking addition' => [
                ServiceOption::sucking(),
            ],
            'welfare specialist cooperation addition' => [
                ServiceOption::welfareSpecialistCooperation(),
            ],
        ];
        $this->should(
            'return a Seq that includes addition when some provision report items contains corresponding option',
            function (ServiceOption $serviceOption): void {
                $dates = Seq::from(
                    Carbon::create(2021, 4, 5),
                    Carbon::create(2021, 4, 10),
                    Carbon::create(2021, 4, 15),
                    Carbon::create(2021, 4, 20),
                    Carbon::create(2021, 4, 25),
                );
                $schedules = $dates
                    ->map(fn (Carbon $date): Schedule => Schedule::create([
                        'date' => $date,
                        'start' => $date->hour(12),
                        'end' => $date->hour(13),
                    ]));
                $results = $schedules
                    ->map(fn (Schedule $schedule): DwsProvisionReportItem => DwsProvisionReportItem::create([
                        'schedule' => $schedule,
                        'category' => DwsProjectServiceCategory::physicalCare(),
                        'headcount' => 1,
                        'options' => [$serviceOption],
                        'note' => '',
                    ]))
                    ->toArray();
                $report = $this->report([
                    'providedIn' => Carbon::create(2021, 4, 1),
                    'plans' => [],
                    'results' => $results,
                ]);
                $chunks = [
                    ...$this->makeChunkWithRange(
                        Carbon::create(2021, 4, 5, 12, 0),
                        Carbon::create(2021, 4, 5, 13, 0),
                    ),
                    ...$this->makeChunkWithRange(
                        Carbon::create(2021, 4, 10, 12, 0),
                        Carbon::create(2021, 4, 10, 13, 0),
                    ),
                    ...$this->makeChunkWithRange(
                        Carbon::create(2021, 4, 15, 12, 0),
                        Carbon::create(2021, 4, 15, 13, 0),
                    ),
                    ...$this->makeChunkWithRange(
                        Carbon::create(2021, 4, 20, 12, 0),
                        Carbon::create(2021, 4, 20, 13, 0),
                    ),
                    ...$this->makeChunkWithRange(
                        Carbon::create(2021, 4, 25, 12, 0),
                        Carbon::create(2021, 4, 25, 13, 0),
                    ),
                ];
                $this->createDwsHomeHelpServiceChunkListUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(...$chunks));

                $actual = $this->interactor->handle(
                    $this->context,
                    $this->providedIn,
                    Option::none(),
                    Option::none(),
                    $this->dwsCertification,
                    $report,
                    Option::none()
                );

                $this->assertMatchesModelSnapshot($actual);
            },
            compact('examples')
        );
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
            '1' => [
                // インターフェース仕様書 設定例 No.1【通常】
                // - 111119 身体日1.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 1, 10, 0),
                    Carbon::create(2021, 4, 1, 11, 30),
                ),

                // インターフェース仕様書 設定例 No.2【ヘルパー要件あり】
                // - 112021 身体重研日2.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 2, 10, 0),
                    Carbon::create(2021, 4, 2, 12, 0),
                    [],
                    ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
                ),

                // インターフェース仕様書 設定例 No.4【同一時間2人派遣】
                // - 111115 身体日1.0
                // - 111116 身体日1.0・2人
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 3, 10, 0),
                    Carbon::create(2021, 4, 3, 11, 0),
                    [],
                    ['headcount' => 2],
                ),

                // インターフェース仕様書 設定例 No.5【2人派遣時間ずれ】
                // - 111131 身体日3.0
                // - 111116 身体日1.0・2人
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 4, 10, 0),
                    Carbon::create(2021, 4, 4, 13, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 4, 10, 0),
                        Carbon::create(2021, 4, 4, 13, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 4, 11, 0),
                        Carbon::create(2021, 4, 4, 12, 0),
                        ['isSecondary' => true],
                    ),
                ),

                // インターフェース仕様書 設定例 No.6【2人派遣ヘルパー要件違い】
                // - 111123 身体日2.0
                // - 111125 身体日2.0・基
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 5, 10, 0),
                    Carbon::create(2021, 4, 5, 13, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 5, 10, 0),
                        Carbon::create(2021, 4, 5, 12, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 5, 11, 0),
                        Carbon::create(2021, 4, 5, 13, 0),
                        ['providerType' => DwsHomeHelpServiceProviderType::beginner()],
                    ),
                ),

                // インターフェース仕様書 設定例 No.8【空き時間あり】
                // - 111635 身体深1.0・早1.0・日1.0
                // - 111827 身体日増0.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 6, 5, 0),
                    Carbon::create(2021, 4, 6, 10, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 6, 5, 0),
                        Carbon::create(2021, 4, 6, 7, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 6, 8, 30),
                        Carbon::create(2021, 4, 6, 10, 0),
                    ),
                ),

                // インターフェース仕様書 設定例 No.9【空き時間複数あり】
                // - 111615 身体深1.0・早1.5・日0.5
                // - 111827 身体日増0.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 7, 5, 0),
                    Carbon::create(2021, 4, 7, 10, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 7, 5, 0),
                        Carbon::create(2021, 4, 7, 6, 15),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 7, 6, 30),
                        Carbon::create(2021, 4, 7, 7, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 7, 8, 45),
                        Carbon::create(2021, 4, 7, 10, 0),
                    ),
                ),

                // インターフェース仕様書 設定例 No.11【0時跨がり】
                // - 111263 身体深2.0
                // - 111599 身体日跨増深2.0・深1.0
                // - 111971 身体深増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 7, 22, 0),
                    Carbon::create(2021, 4, 8, 2, 0),
                ),

                // インターフェース仕様書 設定例 No.12【月跨がり（0時跨がり）】
                // - 111263 身体深2.0
                // - 111599 身体日跨増深2.0・深1.0
                // - 111971 身体深増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 30, 22, 0),
                    Carbon::create(2021, 5, 1, 2, 0),
                ),
            ],
            '2' => [
                // インターフェース仕様書 設定例 No.13【月跨がり（0時跨がり）】
                // - 111251 身体深0.5
                // - 111547 身体日跨増深0.5・深0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 30, 23, 50),
                    Carbon::create(2021, 5, 1, 0, 50),
                ),
            ],
            '3' => [
                // 石丸エラー
                // - 111439 身体日0.5・夜2.5
                // - 111931 身体夜増0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 1, 17, 25),
                    Carbon::create(2021, 4, 1, 20, 55),
                ),
            ],
            '4' => [
                // 家事援助・最小単位（最初の30分）で時間帯跨がり・前半50%以上
                // - 117827 家事深0.5・早0.25
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 1, 5, 40),
                    Carbon::create(2021, 4, 1, 6, 25),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 家事援助・最小単位（最初の30分）で時間帯跨がり・前半50%
                // - 117827 家事深0.5・早0.25
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 2, 5, 45),
                    Carbon::create(2021, 4, 2, 6, 30),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 家事援助・最小単位（最初の30分）で時間帯跨がり・前半50%未満
                // - 117731 家事早0.75
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 3, 5, 50),
                    Carbon::create(2021, 4, 3, 6, 35),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 家事援助・最小単位（15分）で時間帯跨がり・前半50%以上
                // - 117731 家事早0.75
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 4, 7, 20),
                    Carbon::create(2021, 4, 4, 8, 5),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 家事援助・最小単位（15分）で時間帯跨がり・前半50%未満
                // - 117855 家事早0.5・日0.25
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 5, 7, 25),
                    Carbon::create(2021, 4, 5, 8, 10),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),
            ],
            '5' => [
                // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%
                // - 113423 通院1日0.5・夜0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 1, 17, 40),
                    Carbon::create(2021, 4, 1, 18, 40),
                    ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                ),

                // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%
                // - 113423 通院1日0.5・夜0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 2, 17, 45),
                    Carbon::create(2021, 4, 2, 18, 45),
                    ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                ),

                // 通院等介助（身体を伴う）・最小単位（30分）で時間帯跨がり・前半50%未満
                // - 113219 通院1夜1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 3, 17, 50),
                    Carbon::create(2021, 4, 3, 18, 50),
                    ['category' => DwsServiceCodeCategory::accompanyWithPhysicalCare()],
                ),

                // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%超
                // - 117339 通院2夜0.5・深0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 4, 21, 40),
                    Carbon::create(2021, 4, 4, 22, 40),
                    ['category' => DwsServiceCodeCategory::accompany()],
                ),

                // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%
                // - 117339 通院2夜0.5・深0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 5, 21, 45),
                    Carbon::create(2021, 4, 5, 22, 45),
                    ['category' => DwsServiceCodeCategory::accompany()],
                ),

                // 通院等介助（身体を伴わない）・最小単位（30分）で時間帯跨がり・前半50%未満
                // - 117255 通院2深1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 6, 21, 50),
                    Carbon::create(2021, 4, 6, 22, 50),
                    ['category' => DwsServiceCodeCategory::accompany()],
                ),
            ],
            '6' => [
                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例1
                // - 111131 身体日3.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 1, 9, 0),
                    Carbon::create(2021, 4, 1, 12, 0),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例2
                // - 116219 家事夜1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 2, 19, 0),
                    Carbon::create(2021, 4, 2, 20, 0),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例3
                // - 111455 身体日1.0・夜2.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 3, 17, 0),
                    Carbon::create(2021, 4, 3, 20, 0),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例4
                // - 111367 身体早0.5・日1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 4, 7, 45),
                    Carbon::create(2021, 4, 4, 9, 15),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例5
                // - 116323 家事早1.0・日0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 5, 7, 0),
                    Carbon::create(2021, 4, 5, 8, 30),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例6
                // - 111475 身体日2.0・夜1.0
                // - 111951 身体夜増3.0
                // - 111971 身体深増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 6, 16, 0),
                    Carbon::create(2021, 4, 6, 23, 0),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例7
                // - 111379 身体早0.5・日2.5
                // - 111835 身体日増1.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 7, 7, 45),
                    Carbon::create(2021, 4, 7, 12, 15),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例8
                // - 117871 家事早0.75・日0.75
                // - 116387 家事日増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 8, 7, 15),
                    Carbon::create(2021, 4, 8, 9, 45),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例9
                // - 116123 家事日2.0
                // - 116491 家事夜増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 9, 16, 0),
                    Carbon::create(2021, 4, 9, 19, 0),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例10
                // - 116335 家事日1.0・夜0.5
                // - 116487 家事夜増0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 10, 17, 0),
                    Carbon::create(2021, 4, 10, 19, 0),
                    ['category' => DwsServiceCodeCategory::housework()],
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例11
                // - 111255 身体深1.0
                // - 111579 身体日跨増深1.0・深2.0
                // - 111995 身体深増4.0
                // - 111915 身体早増1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 10, 23, 0),
                    Carbon::create(2021, 4, 11, 7, 0),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例12
                // - 111519 身体夜1.0・深2.0
                // - 111987 身体深増3.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 11, 21, 0),
                    Carbon::create(2021, 4, 12, 3, 0),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例13
                // - 111611 身体深0.5・早1.5・日1.0
                // - 111831 身体日増1.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 13, 5, 30),
                    Carbon::create(2021, 4, 13, 11, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 13, 5, 30),
                        Carbon::create(2021, 4, 13, 7, 30),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 13, 9, 0),
                        Carbon::create(2021, 4, 13, 11, 0),
                    ),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例14
                // - 111363 身体早0.5・日0.5
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 14, 7, 45),
                    Carbon::create(2021, 4, 14, 8, 45),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例15
                // - 111447 身体日1.0・夜1.0
                ...$this->makeChunkWithRange(
                    Carbon::create(2021, 4, 15, 16, 50),
                    Carbon::create(2021, 4, 15, 18, 50),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例16
                // - 111115 身体日1.0
                // - 111116 身体日1.0・2人
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 16, 8, 0),
                    Carbon::create(2021, 4, 16, 9, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 16, 8, 0),
                        Carbon::create(2021, 4, 16, 9, 0),
                        ['headcount' => 2],
                    ),
                ),

                // 世田谷区「障害福祉サービス事業者における事務の手引き」居宅介護設定例17
                // - 111131 身体日3.0
                // - 111116 身体日1.0・2人
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 17, 13, 0),
                    Carbon::create(2021, 4, 17, 16, 0),
                    [],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 17, 13, 0),
                        Carbon::create(2021, 4, 17, 16, 0),
                    ),
                    $this->makeFragment(
                        Carbon::create(2021, 4, 17, 14, 0),
                        Carbon::create(2021, 4, 17, 15, 0),
                        ['isSecondary' => true],
                    )
                ),
            ],
            '7' => [
                // 重研 時間帯跨ぎ（深1.0・早2.0）
                // 112095 身体重研深1.0・早2.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 1, 5, 15),
                    Carbon::create(2021, 4, 1, 8, 15),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 2, 5, 15),
                        Carbon::create(2021, 4, 2, 8, 15),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 時間帯跨ぎ（深1.0・早1.5・日0.5）
                // - 112337 身体重研深1.0・早1.5・日0.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 2, 5, 30),
                    Carbon::create(2021, 4, 2, 8, 30),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 2, 5, 30),
                        Carbon::create(2021, 4, 2, 8, 30),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 時間帯跨ぎ（早2.5・日0.5）
                // - 112271 身体重研早2.5・日0.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 3, 5, 45),
                    Carbon::create(2021, 4, 3, 8, 45),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 3, 5, 45),
                        Carbon::create(2021, 4, 3, 8, 45),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 時間帯跨ぎ（早2.0・日1.0）
                // - 112103 身体重研早2.0・日1.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 4, 6, 00),
                    Carbon::create(2021, 4, 4, 9, 00),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 4, 6, 0),
                        Carbon::create(2021, 4, 4, 9, 0),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 時間帯跨ぎ（日1.0）
                // - 112019 身体重研日1.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 5, 8, 00),
                    Carbon::create(2021, 4, 5, 8, 30),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 5, 8, 0),
                        Carbon::create(2021, 4, 5, 8, 30),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深1.0・深2.0）
                // - 112073 身体重研深1.0
                // - 112119 身体重研日跨増深1.0・深2.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 6, 23, 15),
                    Carbon::create(2021, 4, 7, 2, 15),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 6, 23, 15),
                        Carbon::create(2021, 4, 7, 2, 15),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深1.0・深2.0）
                // - 112073 身体重研深1.0
                // - 112119 身体重研日跨増深1.0・深2.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 8, 23, 30),
                    Carbon::create(2021, 4, 9, 2, 30),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 8, 23, 30),
                        Carbon::create(2021, 4, 9, 2, 30),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深1.0・深2.0）
                // - 112073 身体重研深1.0
                // - 112119 身体重研日跨増深1.0・深2.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 10, 23, 45),
                    Carbon::create(2021, 4, 11, 2, 45),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 10, 23, 45),
                        Carbon::create(2021, 4, 11, 2, 45),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深2.0・深1.0）
                // - 112075 身体重研深2.0
                // - 112119 身体重研日跨増深2.0・深1.0
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 12, 22, 15),
                    Carbon::create(2021, 4, 13, 1, 15),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 12, 22, 15),
                        Carbon::create(2021, 4, 13, 1, 15),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深1.5・深1.5）
                // - 112073 身体重研深1.5
                // - 112119 身体重研日跨増深1.5・深1.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 14, 22, 30),
                    Carbon::create(2021, 4, 15, 1, 30),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 14, 22, 30),
                        Carbon::create(2021, 4, 15, 1, 30),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
                // 重研 日跨ぎ（深1.5・深1.5）
                // - 112073 身体重研深1.5
                // - 112119 身体重研日跨増深1.5・深1.5
                ...$this->makeChunkWithFragments(
                    Carbon::create(2021, 4, 16, 22, 45),
                    Carbon::create(2021, 4, 17, 1, 45),
                    ['category' => DwsServiceCodeCategory::physicalCare()],
                    $this->makeFragment(
                        Carbon::create(2021, 4, 16, 22, 45),
                        Carbon::create(2021, 4, 17, 1, 45),
                        ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()]
                    ),
                ),
            ],
        ];
    }

    /**
     * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder} のモック実装.
     *
     * @param array $filterParams
     * @return \Domain\FinderResult
     */
    private function findEntry(array $filterParams): FinderResult
    {
        $category = $filterParams['category'];
        $seq = self::$entriesMap[$category->value()];
        if (isset($filterParams['isSecondary'])) {
            $list = $seq->filter(function (DwsHomeHelpServiceDictionaryEntry $x) use ($filterParams): bool {
                return $x->isSecondary === $filterParams['isSecondary']
                    && $x->isExtra === $filterParams['isExtra']
                    && $x->isPlannedByNovice === $filterParams['isPlannedByNovice']
                    && $x->providerType === $filterParams['providerType']
                    && (
                        empty($filterParams['morningDuration'])
                        || self::between($filterParams['morningDuration'], $x->morningDuration)
                    )
                    && (
                        empty($filterParams['daytimeDuration'])
                        || self::between($filterParams['daytimeDuration'], $x->daytimeDuration)
                    )
                    && (
                        empty($filterParams['nightDuration'])
                        || self::between($filterParams['nightDuration'], $x->nightDuration)
                    )
                    && (
                        empty($filterParams['midnightDuration1'])
                        || self::between($filterParams['midnightDuration1'], $x->midnightDuration1)
                    )
                    && (
                        empty($filterParams['midnightDuration2'])
                        || self::between($filterParams['midnightDuration2'], $x->midnightDuration2)
                    );
            });
            return FinderResult::from($list->take(1), Pagination::create([]));
        } else {
            return FinderResult::from($seq->take(1), Pagination::create([]));
        }
    }

    /**
     * {@link \UseCase\Billing\ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase} のモック実装.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore 加算対象の単位数
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|\ScalikePHP\Option $dictionaryEntryOption
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
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[][]|\ScalikePHP\Seq[]
     */
    private static function createEntriesMap(): array
    {
        return Seq::from(...self::readEntries())
            ->groupBy(fn (DwsHomeHelpServiceDictionaryEntry $x): int => $x->category->value())
            ->toAssoc();
    }

    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|iterable
     */
    private static function readEntries(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('Billing/dict-dws-11_R304.csv');
        $data = Csv::read($csv);
        foreach (DwsHomeHelpServiceDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }
}
