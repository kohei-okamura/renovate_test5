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
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 居宅 請求生成テスト.
 * 設定例No.5, No.6
 */
class CreateDwsBillingHomeHelpServiceNo5No6Cest extends CreateDwsBillingTest
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
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 5),
                    'start' => Carbon::create(2021, 4, 5, 10, 0),
                    'end' => Carbon::create(2021, 4, 5, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.5',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 5),
                    'start' => Carbon::create(2021, 4, 5, 11, 0),
                    'end' => Carbon::create(2021, 4, 5, 13, 0),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.5',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 6),
                    'start' => Carbon::create(2021, 4, 6, 10, 0),
                    'end' => Carbon::create(2021, 4, 6, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.6',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 6),
                    'start' => Carbon::create(2021, 4, 6, 11, 0),
                    'end' => Carbon::create(2021, 4, 6, 13, 0),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [ServiceOption::providedByBeginner()],
                'note' => '設定例No.6',
            ]),
        ];

        $repository = $this->getProvisionReportRepository();
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => $reportResults,
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 10),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2021-05',
        ]);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

        // Billing
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing1 = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();

        // Bundle
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billing1->id)->head()[1];
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
        $statement = $statements->head();

        // 明細書明細
        $I->assertNotEmpty($statement->items);
        $I->assertArrayStrictEquals([
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111131'), // 身体日中3.0
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 833,
                count: 1,
                totalScore: 833,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111116'), // 身体日1.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 402,
                count: 1,
                totalScore: 402,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111123'), // 身体日中2.0
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 666,
                count: 1,
                totalScore: 666,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111125'), // 身体日中2.0基
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 466,
                count: 1,
                totalScore: 466,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('11ZZ01'), // COVID-19加算(0.1%)
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 2,
                count: 1,
                totalScore: 2,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('116010'), // 特定事業所加算1(20%)
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 474,
                count: 1,
                totalScore: 474,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('116710'), // 処遇加算加算2 (20%)
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 569, // 2843 * 20%
                count: 1,
                totalScore: 569,
            ),
        ], $statement->items);
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
        $statement = $statements->head();

        // 明細書集計
        $I->assertNotEmpty($statement->aggregates);
        $I->assertArrayStrictEquals([
            new DwsBillingStatementAggregate(
                serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                startedOn: $this->examples
                    ->contracts[16]
                    ->dwsPeriods[DwsServiceDivisionCode::homeHelpService()->value()]
                    ->start,
                terminatedOn: $this->examples
                    ->contracts[16]
                    ->dwsPeriods[DwsServiceDivisionCode::homeHelpService()->value()]
                    ->end,
                serviceDays: 2,
                subtotalScore: 3412,
                unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                subtotalFee: 38214,
                unmanagedCopay: 3821,
                managedCopay: 3821,
                cappedCopay: 3821,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 3821,
                subtotalBenefit: 34393,
                subtotalSubsidy: null,
            ),
        ], $statement->aggregates);
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

        $expectAggregate = DwsBillingServiceReportAggregate::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(6_0000),
                DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::fromInt(2_0000),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(8_0000),
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
        ]);
        // サービス提供実績記録票：合計(計画)
        $I->assertNotEmpty($serviceReport->plan);
        $I->assertEquals($expectAggregate, $serviceReport->plan);
        // サービス提供実績記録票：合計(実績)
        $I->assertNotEmpty($serviceReport->result);
        $I->assertEquals($expectAggregate, $serviceReport->result);
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

        // サービス提供実績記録票：明細
        $baseItem = DwsBillingServiceReportItem::create([
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
            $baseItem->copy([
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2021, 4, 5),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 10, 0),
                        'end' => Carbon::create(2021, 4, 5, 13, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 10, 0),
                        'end' => Carbon::create(2021, 4, 5, 13, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'providedOn' => Carbon::create(2021, 4, 5),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 11, 0),
                        'end' => Carbon::create(2021, 4, 5, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 11, 0),
                        'end' => Carbon::create(2021, 4, 5, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 3,
                'providedOn' => Carbon::create(2021, 4, 6),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 6, 10, 0),
                        'end' => Carbon::create(2021, 4, 6, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 6, 10, 0),
                        'end' => Carbon::create(2021, 4, 6, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 4,
                'providedOn' => Carbon::create(2021, 4, 6),
                'providerType' => DwsBillingServiceReportProviderType::beginner(),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 6, 11, 0),
                        'end' => Carbon::create(2021, 4, 6, 13, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 6, 11, 0),
                        'end' => Carbon::create(2021, 4, 6, 13, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
