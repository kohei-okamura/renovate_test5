<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\DwsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 居宅 請求生成テスト.
 * 設定例 No.9
 */
class CreateDwsBillingHomeHelpServiceNo9Cest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API呼び出しテスト.
     *
     * @param \BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API Call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[0];

        // 予実を準備
        $baseItem = DwsProvisionReportItem::create([
            'category' => DwsProjectServiceCategory::physicalCare(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'options' => [],
            'note' => '設定例No.9',
        ]);
        $reportResults = [
            $baseItem->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 3),
                    'start' => Carbon::create(2021, 4, 3, 5, 0),
                    'end' => Carbon::create(2021, 4, 3, 6, 15),
                ]),
            ]),
            $baseItem->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 3),
                    'start' => Carbon::create(2021, 4, 3, 6, 30),
                    'end' => Carbon::create(2021, 4, 3, 7, 30),
                ]),
            ]),
            $baseItem->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 3),
                    'start' => Carbon::create(2021, 4, 3, 8, 45),
                    'end' => Carbon::create(2021, 4, 3, 10, 0),
                ]),
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 10),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', ['officeId' => $office->id, 'transactedIn' => '2021-05']);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

        // データ検証
        $certification = $this->examples->dwsCertifications[15];
        // Billing
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();

        // Bundle
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        $actualBundle = $bundles->head();

        //
        // 検証
        //
        $this->checkStatementItems($I, $actualBundle);
        $this->checkStatementAggregates($I, $actualBundle);
        $this->checkServiceReportAggregates($I, $actualBundle);
        $this->checkServiceReportItems($I, $actualBundle);
    }

    /**
     * 請求書明細の検証.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
    private function checkStatementItems(BillingTester $I, DwsBillingBundle $actualBundle)
    {
        // Statement
        $statementRepository = $this->getStatementRepository();
        /** @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $statementRepository->lookupByBundleId($actualBundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $actualStatement */
        $actualStatement = $statements->head();

        $I->assertNotEmpty($actualStatement->items);
        $I->assertArrayStrictEquals([
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111615'), // 身体深1.0、早1.5、日0.5
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 1121,
                count: 1,
                totalScore: 1121,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111827'), // 身体日増0.5
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 83,
                count: 1,
                totalScore: 83,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('11ZZ01'), // COVID-19加算
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 1, // 1204
                count: 1,
                totalScore: 1,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('116010'), // 特定事業所加算1(20%)
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 241, // 1205 * 20%
                count: 1,
                totalScore: 241,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('116710'), // 処遇加算加算2 (20%)
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 289, // 1446 * 20%
                count: 1,
                totalScore: 289,
            ),
        ], $actualStatement->items);
    }

    /**
     * 明細書集計の検証.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
    private function checkStatementAggregates(BillingTester $I, DwsBillingBundle $actualBundle)
    {
        // Statement
        $statementRepository = $this->getStatementRepository();
        /** @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $statementRepository->lookupByBundleId($actualBundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $actualStatement */
        $actualStatement = $statements->head();

        $I->assertMatchesModelSnapshot($actualStatement->aggregates);
    }

    /**
     * サービス実績記録票 合計を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
    private function checkServiceReportAggregates(BillingTester $I, DwsBillingBundle $actualBundle)
    {
        // ServiceReport
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($actualBundle->id)->values()->flatten();
        $I->assertCount(1, $serviceReports);
        /** @var \Domain\Billing\DwsBillingServiceReport $serviceReport */
        $serviceReport = $serviceReports->head();

        $I->assertEquals(DwsBillingServiceReportAggregate::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::housework()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
        ]), $serviceReport->plan);

        $I->assertEquals(DwsBillingServiceReportAggregate::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(3_5000),
                DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_5000),
            ],
            DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::housework()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
            DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
            ],
        ]), $serviceReport->result);
    }

    /**
     * サービス実績記録票 明細を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
    private function checkServiceReportItems(BillingTester $I, DwsBillingBundle $actualBundle)
    {
        // ServiceReport
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($actualBundle->id)->values()->flatten();
        $I->assertCount(1, $serviceReports);
        /** @var \Domain\Billing\DwsBillingServiceReport $serviceReport */
        $serviceReport = $serviceReports->head();

        $baseReportItem = DwsBillingServiceReportItem::create([
            'serialNumber' => 1,
            'providedOn' => Carbon::create(2021, 4, 3),
            'serviceType' => DwsGrantedServiceCode::physicalCare(),
            'providerType' => DwsBillingServiceReportProviderType::novice(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'serviceCount' => 0,
            'headcount' => 1,
            'isCoaching' => false,
            'isFirstTime' => false,
            'isEmergency' => false,
            'isWelfareSpecialistCooperation' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'isMovingCareSupport' => false,
            'isDriving' => false,
            'isPreviousMonth' => false,
            'note' => '',
        ]);
        $I->assertNotEmpty($serviceReport->items);
        $I->assertArrayStrictEquals([
            $baseReportItem->copy([
                'plan' => null,
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 3, 5, 0),
                        'end' => Carbon::create(2021, 4, 3, 6, 15),
                    ]),
                ]),
            ]),
            $baseReportItem->copy([
                'plan' => null,
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 3, 6, 30),
                        'end' => Carbon::create(2021, 4, 3, 7, 30),
                    ]),
                ]),
            ]),
            $baseReportItem->copy([
                'plan' => null,
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 3, 8, 45),
                        'end' => Carbon::create(2021, 4, 3, 10, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_5000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
