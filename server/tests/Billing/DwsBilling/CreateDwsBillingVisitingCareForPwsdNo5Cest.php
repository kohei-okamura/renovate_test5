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
 * 設定例No.5
 */
final class CreateDwsBillingVisitingCareForPwsdNo5Cest extends CreateDwsBillingTest
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
                    'date' => Carbon::create(2021, 4, 5),
                    'start' => Carbon::create(2021, 4, 5, 4, 0),
                    'end' => Carbon::create(2021, 4, 5, 9, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 180,
                'options' => [],
                'note' => '設定例No.5',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 5),
                    'start' => Carbon::create(2021, 4, 5, 6, 0),
                    'end' => Carbon::create(2021, 4, 5, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 180,
                'options' => [],
                'note' => '設定例No.5',
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
                serviceCode: ServiceCode::fromString('124371'), // 重訪3深夜1.0
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
                serviceCode: ServiceCode::fromString('122531'), // 重訪Ⅲ早朝4.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121321'), // 重訪Ⅲ日中8.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 8,
                totalScore: 680,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122372'), // 重訪Ⅲ早朝1.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 231,
                count: 1,
                totalScore: 231,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122382'), // 重訪Ⅲ早朝1.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122492'), // 重訪Ⅲ早朝2.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121502'), // 重訪Ⅲ日中2.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 91,
                count: 1,
                totalScore: 91,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121512'), // 重訪Ⅲ日中3.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 92,
                count: 1,
                totalScore: 92,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128453'), // 移動加算 1.0
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 100,
                count: 1,
                totalScore: 100,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128457'), // 移動加算 1.5
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128461'), // 移動加算 2.0
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128465'), // 移動加算 2.5
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128469'), // 移動加算 3.0
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128454'), // 移動加算 1.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 100,
                count: 1,
                totalScore: 100,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128458'), // 移動加算 1.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128462'), // 移動加算 2.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128466'), // 移動加算 2.5・2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128470'), // 移動加算 3.0・2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 令和3年9月30日までの上乗せ分（重訪）
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 2, // 移動介護加算を除く 2,330 単位 × 0.1% = 2.33 を四捨五入
                count: 1,
                totalScore: 2,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 466, // 移動介護加算を除く (2,330 + 2) = 2,332 単位 × 20% = 466.4 を四捨五入
                count: 1,
                totalScore: 466,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 467, // 合計 3,198 単位 × 14.6% = 466.908 を四捨五入
                count: 1,
                totalScore: 467,
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
                'serviceCount' => 1,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 4, 0),
                        'end' => Carbon::create(2021, 4, 5, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(8_0000),
                    'movingDurationHours' => Decimal::fromInt(3_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 4, 0),
                        'end' => Carbon::create(2021, 4, 5, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(8_0000),
                    'movingDurationHours' => Decimal::fromInt(3_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'providedOn' => Carbon::create(2021, 4, 5),
                'serviceCount' => 2,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 6, 0),
                        'end' => Carbon::create(2021, 4, 5, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                    'movingDurationHours' => Decimal::fromInt(3_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 6, 0),
                        'end' => Carbon::create(2021, 4, 5, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                    'movingDurationHours' => Decimal::fromInt(3_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
