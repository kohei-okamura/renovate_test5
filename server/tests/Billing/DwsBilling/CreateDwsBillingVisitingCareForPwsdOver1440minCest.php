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
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 重訪 請求テスト.
 * 日跨ぎ2人1人混合予実計1440分超え
 */
final class CreateDwsBillingVisitingCareForPwsdOver1440minCest extends CreateDwsBillingTest
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
        $reportItems = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 5, 6),
                    'start' => Carbon::create(2021, 5, 6, 19, 0),
                    'end' => Carbon::create(2021, 5, 7, 9, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 5, 7),
                    'start' => Carbon::create(2021, 5, 7, 9, 0),
                    'end' => Carbon::create(2021, 5, 7, 20, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 5, 7),
                    'start' => Carbon::create(2021, 5, 7, 19, 0),
                    'end' => Carbon::create(2021, 5, 7, 20, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 5, 7),
                    'start' => Carbon::create(2021, 5, 7, 20, 0),
                    'end' => Carbon::create(2021, 5, 8, 9, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '',
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[3]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => $reportItems,
            'results' => $reportItems,
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

    /**
     * 明細書明細行の検証.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\DwsBillingBundle $actualBundle
     */
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
                serviceCode: ServiceCode::fromString('123371'), // 重訪Ⅲ夜間1.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 231,
                count: 1,
                totalScore: 231,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123381'), // 重訪Ⅲ夜間1.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123491'), // 重訪Ⅲ夜間2.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123501'), // 重訪Ⅲ夜間2.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 114,
                count: 1,
                totalScore: 114,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123511'), // 重訪Ⅲ夜間3.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124521'), // 重訪Ⅲ深夜3.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 3,
                totalScore: 405,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124531'), // 重訪Ⅲ深夜4.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 3,
                totalScore: 414,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124321'), // 重訪Ⅲ深夜8.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 128,
                count: 10,
                totalScore: 1280,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123372'), // 重訪Ⅲ夜間1.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 231,
                count: 1,
                totalScore: 231,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123382'), // 重訪Ⅲ夜間1.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123492'), // 重訪Ⅲ夜間2.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123502'), // 重訪Ⅲ夜間2.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 114,
                count: 1,
                totalScore: 114,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123512'), // 重訪Ⅲ夜間3.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124522'), // 重訪Ⅲ深夜3.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 2,
                totalScore: 270,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124532'), // 重訪Ⅲ深夜4.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 2,
                totalScore: 276,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124322'), // 重訪Ⅲ深夜8.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 128,
                count: 6,
                totalScore: 768,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124371'), // 重訪Ⅲ深夜1.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 278,
                count: 2,
                totalScore: 556,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124381'), // 重訪Ⅲ深夜1.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 2,
                totalScore: 270,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124491'), // 重訪Ⅲ深夜2.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 2,
                totalScore: 276,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124501'), // 重訪Ⅲ深夜2.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 137,
                count: 2,
                totalScore: 274,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124511'), // 重訪Ⅲ深夜3.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 2,
                totalScore: 276,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122321'), // 重訪Ⅲ早朝8.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 106,
                count: 8,
                totalScore: 848,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121331'), // 重訪Ⅲ日中12.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 10,
                totalScore: 850,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121341'), // 重訪Ⅲ日中16.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 80,
                count: 8,
                totalScore: 640,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121351'), // 重訪Ⅲ日中20.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 86,
                count: 4,
                totalScore: 344,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123351'), // 重訪Ⅲ夜間20.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 108,
                count: 4,
                totalScore: 432,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123361'), // 重訪Ⅲ夜間24.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 100,
                count: 4,
                totalScore: 400,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124361'), // 重訪Ⅲ深夜24.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 120,
                count: 4,
                totalScore: 480,
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
                serviceCode: ServiceCode::fromString('124502'), // 重訪Ⅲ深夜2.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 137,
                count: 1,
                totalScore: 137,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('124512'), // 重訪Ⅲ深夜3.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                totalScore: 138,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122322'), // 重訪Ⅲ早朝8.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 106,
                count: 4,
                totalScore: 424,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121332'), // 重訪Ⅲ日中12.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 2,
                totalScore: 170,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123332'), // 重訪Ⅲ夜間12.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 106,
                count: 2,
                totalScore: 212,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 令和3年9月30日までの上乗せ分（重訪）
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 12, // これまでの合計 12,067 × 0.1% = 12.067 を四捨五入
                count: 1,
                totalScore: 12,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 2416, // これまでの合計 (12,067 + 12) = 12,079 単位 × 20% = 2415.8 を四捨五入
                count: 1,
                totalScore: 2416,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 2116, // これまでの合計 14,495単位 × 14.6% = 2,116.27 を四捨五入
                count: 1,
                totalScore: 2116,
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
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();

        // 明細書集計
        $I->assertMatchesModelSnapshot($statement->aggregates);
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

        // サービス提供実績記録票：合計(計画)
        $I->assertNotEmpty($serviceReport->plan);
        $I->assertMatchesJsonSnapshot($serviceReport->plan->toAssoc());
        // サービス提供実績記録票：合計(実績)
        $I->assertNotEmpty($serviceReport->result);
        $I->assertMatchesJsonSnapshot($serviceReport->result->toAssoc());
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
            'serialNumber' => 1,
            'serviceCount' => 0,
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
                'headcount' => 2,
                'serialNumber' => 1,
                'serviceCount' => 0,
                'providedOn' => Carbon::create(2021, 5, 6),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 6, 19, 0),
                        'end' => Carbon::create(2021, 5, 7, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(5_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 6, 19, 0),
                        'end' => Carbon::create(2021, 5, 7, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(5_0000),
                ]),
            ]),
            $baseItem->copy([
                'headcount' => 1,
                'serialNumber' => 2,
                'serviceCount' => 1,
                'providedOn' => Carbon::create(2021, 5, 7),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 0, 0),
                        'end' => Carbon::create(2021, 5, 8, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(24_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 0, 0),
                        'end' => Carbon::create(2021, 5, 8, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(24_0000),
                ]),
            ]),
            $baseItem->copy([
                'headcount' => 1,
                'serviceCount' => 2,
                'serialNumber' => 3,
                'providedOn' => Carbon::create(2021, 5, 7),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 0, 0),
                        'end' => Carbon::create(2021, 5, 7, 9, 0),
                    ]),
                    'serviceDurationHours' => null,
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 0, 0),
                        'end' => Carbon::create(2021, 5, 7, 9, 0),
                    ]),
                    'serviceDurationHours' => null,
                ]),
            ]),
            $baseItem->copy([
                'headcount' => 1,
                'serviceCount' => 2,
                'serialNumber' => 3,
                'providedOn' => Carbon::create(2021, 5, 7),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 19, 0),
                        'end' => Carbon::create(2021, 5, 7, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 7, 19, 0),
                        'end' => Carbon::create(2021, 5, 7, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_0000),
                ]),
            ]),
            $baseItem->copy([
                'headcount' => 1,
                'serialNumber' => 4,
                'providedOn' => Carbon::create(2021, 5, 8),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 8, 0, 0),
                        'end' => Carbon::create(2021, 5, 8, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(9_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 8, 0, 0),
                        'end' => Carbon::create(2021, 5, 8, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(9_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
