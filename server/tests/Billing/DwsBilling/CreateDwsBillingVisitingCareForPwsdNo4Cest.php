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
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 重訪 請求テスト.
 * 設定例No.4
 */
final class CreateDwsBillingVisitingCareForPwsdNo4Cest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API呼び出しテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedAPICall(BillingTester $I): void
    {
        $I->wantTo('succeed API Call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[2];

        // 予実を準備
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 4),
                    'start' => Carbon::create(2021, 4, 4, 4, 0),
                    'end' => Carbon::create(2021, 4, 4, 7, 30),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.4',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 4),
                    'start' => Carbon::create(2021, 4, 4, 9, 0),
                    'end' => Carbon::create(2021, 4, 4, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.4',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 4),
                    'start' => Carbon::create(2021, 4, 4, 14, 0),
                    'end' => Carbon::create(2021, 4, 4, 17, 30),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.4',
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[3]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => $reportResults,
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', ['transactedIn' => '2021-05', 'officeId' => $office->id]);

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
        // パラメータの検証
        //
        $this->checkStatementItems($I, $actualBundle);
        $this->checkStatementAggregates($I, $actualBundle);
        $this->checkServiceReportAggregates($I, $actualBundle);
        $this->checkServiceReportItems($I, $actualBundle);
    }

    private function checkStatementItems(BillingTester $I, DwsBillingBundle $actualBundle): void
    {
        // Statement
        $statementRepository = $this->getStatementRepository();
        /** @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $statementRepository->lookupByBundleId($actualBundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();

        // 明細書明細
        $I->assertNotEmpty($statement->items);
        $I->assertArrayStrictEquals([
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124371'), // 重訪Ⅲ深夜1.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 278,
                count: 1,
                totalScore: 278,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124381'), // 重訪Ⅲ深夜1.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 1,
                totalScore: 135,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124491'), // 重訪Ⅲ深夜2.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                totalScore: 138,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122501'), // 重訪Ⅲ早朝2.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 114,
                count: 1,
                totalScore: 114,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122511'), // 重訪Ⅲ早朝3.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122521'), // 重訪Ⅲ早朝3.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121531'), // 重訪Ⅲ日中4.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 92,
                count: 1,
                totalScore: 92,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121321'), // 重訪Ⅲ日中8.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 8,
                totalScore: 680,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121331'), // 重訪Ⅲ日中12.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 4,
                totalScore: 340,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124372'), // 重訪Ⅲ深夜1.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 278,
                count: 1,
                totalScore: 278,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124382'), // 重訪Ⅲ深夜1.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 1,
                totalScore: 135,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124492'), // 重訪Ⅲ深夜2.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                totalScore: 138,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122502'), // 重訪Ⅲ早朝2.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 114,
                count: 1,
                totalScore: 114,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122512'), // 重訪Ⅲ早朝3.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122522'), // 重訪Ⅲ早朝3.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121532'), // 重訪Ⅲ日中4.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 92,
                count: 1,
                totalScore: 92,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121322'), // 重訪Ⅲ日中8.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 8,
                totalScore: 680,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121332'), // 重訪Ⅲ日中12.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 4,
                totalScore: 340,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 令和3年9月30日までの上乗せ分（重訪）
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 4, // 合計 4,010 単位 × 0.1% = 4.01 を四捨五入
                count: 1,
                totalScore: 4,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 803, // 合計 4,014 単位 × 20% = 802.8 を四捨五入
                count: 1,
                totalScore: 803,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 703, // 合計 4,817 単位 × 14.6% = 703.282 を四捨五入
                count: 1,
                totalScore: 703,
            ),
        ], $statement->items, Json::encode($statement->items));
    }

    /**
     * 明細書集計の検証.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
    private function checkStatementAggregates(BillingTester $I, DwsBillingBundle $actualBundle): void
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
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                startedOn: $this->examples
                    ->contracts[17]
                    ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                    ->start,
                terminatedOn: $this->examples
                    ->contracts[17]
                    ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                    ->end,
                serviceDays: 1,
                subtotalScore: 5520,
                unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                subtotalFee: 61824,
                unmanagedCopay: 6182,
                managedCopay: 6182,
                cappedCopay: 6182,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 6182,
                subtotalBenefit: 55642,
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
    private function checkServiceReportAggregates(BillingTester $I, DwsBillingBundle $actualBundle): void
    {
        // ServiceReport
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($actualBundle->id)->values()->flatten();
        $I->assertCount(1, $serviceReports);
        /** @var \Domain\Billing\DwsBillingServiceReport $serviceReport */
        $serviceReport = $serviceReports->head();

        $expectAggregate = DwsBillingServiceReportAggregate::fromAssoc([
            DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(20_0000),
            ],
            DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
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
    private function checkServiceReportItems(BillingTester $I, DwsBillingBundle $actualBundle): void
    {
        // ServiceReport
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($actualBundle->id)->values()->flatten();
        $I->assertCount(1, $serviceReports);
        /** @var \Domain\Billing\DwsBillingServiceReport $serviceReport */
        $serviceReport = $serviceReports->head();

        // サービス提供実績記録票：明細
        $baseItem = DwsBillingServiceReportItem::create([
            'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
            'providerType' => DwsBillingServiceReportProviderType::none(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'serviceCount' => 0,
            'headcount' => 2,
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
                'providedOn' => Carbon::create(2021, 4, 4),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 4, 0),
                        'end' => Carbon::create(2021, 4, 4, 7, 30),
                    ]),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 4, 0),
                        'end' => Carbon::create(2021, 4, 4, 7, 30),
                    ]),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2021, 4, 4),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 9, 0),
                        'end' => Carbon::create(2021, 4, 4, 12, 0),
                    ]),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 9, 0),
                        'end' => Carbon::create(2021, 4, 4, 12, 0),
                    ]),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2021, 4, 4),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 14, 0),
                        'end' => Carbon::create(2021, 4, 4, 17, 30),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 14, 0),
                        'end' => Carbon::create(2021, 4, 4, 17, 30),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
