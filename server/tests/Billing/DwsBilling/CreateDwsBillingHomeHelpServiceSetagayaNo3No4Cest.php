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
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 居宅 請求生成テスト.
 * 世田谷区手引き 設定例No.3, No.4 (身体介護）
 */
final class CreateDwsBillingHomeHelpServiceSetagayaNo3No4Cest extends CreateDwsBillingTest
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
                    'date' => Carbon::create(2021, 4, 3),
                    'start' => Carbon::create(2021, 4, 3, 17, 0),
                    'end' => Carbon::create(2021, 4, 3, 20, 0),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '世田谷区手引き設定例No.3',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 4),
                    'start' => Carbon::create(2021, 4, 4, 7, 45),
                    'end' => Carbon::create(2021, 4, 4, 9, 15),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '世田谷区手引き設定例No.4',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 5),
                    'start' => Carbon::create(2021, 4, 5, 7, 50),
                    'end' => Carbon::create(2021, 4, 5, 9, 20),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '世田谷区手引き設定例No.4 最小時間より短い',
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
            'fixedAt' => Carbon::create(2021, 5, 1),
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
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();

        // Bundle
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        $bundle = $bundles->head();

        //
        // 検証
        //
        $this->checkStatementItems($I, $bundle);
        $this->checkStatementAggregates($I, $bundle);
        $this->checkServiceReportAggregates($I, $bundle);
        $this->checkServiceReportItems($I, $bundle);
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
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();

        // 明細書明細
        $I->assertNotEmpty($statement->items);
        $I->assertArrayStrictEquals([
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111455'), // 身体日中1.0夜間2.0
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 941,
                count: 1,
                totalScore: 941,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111367'), // 身体早朝0.5日中1.0
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 648,
                count: 1,
                totalScore: 648,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('111119'), // 身体日中1.5
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 584,
                count: 1,
                totalScore: 584,
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
                unitScore: 435, // 2175
                count: 1,
                totalScore: 435,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('116710'), // 処遇加算加算2 (20%)
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 522, // 2610 * 20%
                count: 1,
                totalScore: 522,
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
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();

        $I->assertMatchesModelSnapshot($statement->aggregates);
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
                'providedOn' => Carbon::create(2021, 4, 3),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 3, 17, 0),
                        'end' => Carbon::create(2021, 4, 3, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 3, 17, 0),
                        'end' => Carbon::create(2021, 4, 3, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(3_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'providedOn' => Carbon::create(2021, 4, 4),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 7, 45),
                        'end' => Carbon::create(2021, 4, 4, 9, 15),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_5000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 4, 7, 45),
                        'end' => Carbon::create(2021, 4, 4, 9, 15),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_5000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 3,
                'providedOn' => Carbon::create(2021, 4, 5),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 7, 50),
                        'end' => Carbon::create(2021, 4, 5, 9, 20),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_5000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 5, 7, 50),
                        'end' => Carbon::create(2021, 4, 5, 9, 20),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(1_5000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
