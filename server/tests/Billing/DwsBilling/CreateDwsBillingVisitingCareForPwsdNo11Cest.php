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
 * 設定例No.11
 */
final class CreateDwsBillingVisitingCareForPwsdNo11Cest extends CreateDwsBillingTest
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
                    'date' => Carbon::create(2021, 4, 11),
                    'start' => Carbon::create(2021, 4, 11, 6, 00),
                    'end' => Carbon::create(2021, 4, 11, 10, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 240,
                'options' => [],
                'note' => '設定例No.11',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 11),
                    'start' => Carbon::create(2021, 4, 11, 8, 00),
                    'end' => Carbon::create(2021, 4, 11, 12, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 240,
                'options' => [],
                'note' => '設定例No.11',
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
                serviceCode: ServiceCode::fromString('122371'), // 重訪Ⅲ早朝1.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 231,
                count: 1,
                totalScore: 231,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122381'), // 重訪Ⅲ早朝1.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 113,
                count: 1,
                totalScore: 113,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('122491'), // 重訪Ⅲ早朝2.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 115,
                count: 1,
                totalScore: 115,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121501'), // 重訪Ⅲ日中2.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 91,
                count: 1,
                totalScore: 91,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121511'), // 重訪Ⅲ日中3.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 92,
                count: 1,
                totalScore: 92,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121521'), // 重訪Ⅲ日中3.5
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 90,
                count: 1,
                totalScore: 90,
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
                count: 4,
                totalScore: 340,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121372'), // 重訪Ⅲ日中1.0 2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 185,
                count: 1,
                totalScore: 185,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121382'), // 重訪Ⅲ日中1.0 2人
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 90,
                count: 1,
                totalScore: 90,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121492'), // 重訪Ⅲ日中2.0 2人
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
                serviceCode: ServiceCode::fromString('128473'), // 移動加算 4.0
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 50,
                count: 1,
                totalScore: 50,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128454'), // 移動加算 1.0 2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 100,
                count: 1,
                totalScore: 100,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128458'), // 移動加算 1.5 2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('128462'), // 移動加算 2.0 2人
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                totalScore: 25,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 重訪 COVID-19 加算
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 2, // 移動加算を除く合計 1,531 単位の 0.1% = 1.531 を四捨五入
                count: 1,
                totalScore: 2,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 307, // 移動加算を除く合計 (1,531 + 2) = 1,533 単位の 20% = 306.6 を四捨五入
                count: 1,
                totalScore: 307,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 327, // ここまでの合計 2,240 単位の 14.6% = 327.04 を四捨五入
                count: 1,
                totalScore: 327,
            ),
        ], $statement->items, Json::encode($statement->items));
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

        // 明細書集計
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
                'providedOn' => Carbon::create(2021, 4, 11),
                'serviceCount' => 1,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 11, 6, 0),
                        'end' => Carbon::create(2021, 4, 11, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(6_0000),
                    'movingDurationHours' => Decimal::fromInt(4_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 11, 6, 0),
                        'end' => Carbon::create(2021, 4, 11, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(6_0000),
                    'movingDurationHours' => Decimal::fromInt(4_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'providedOn' => Carbon::create(2021, 4, 11),
                'serviceCount' => 2,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 11, 8, 0),
                        'end' => Carbon::create(2021, 4, 11, 10, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                    'movingDurationHours' => Decimal::fromInt(2_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 11, 8, 0),
                        'end' => Carbon::create(2021, 4, 11, 10, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(2_0000),
                    'movingDurationHours' => Decimal::fromInt(2_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
