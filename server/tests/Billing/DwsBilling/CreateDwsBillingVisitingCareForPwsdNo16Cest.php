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
use Domain\Shift\ServiceOption;
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 重訪 請求テスト.
 * 設定例No.16
 */
final class CreateDwsBillingVisitingCareForPwsdNo16Cest extends CreateDwsBillingTest
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
                    'date' => Carbon::create(2021, 4, 16),
                    'start' => Carbon::create(2021, 4, 16, 8, 0),
                    'end' => Carbon::create(2021, 4, 16, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [ServiceOption::coaching()],
                'note' => '設定例No.16',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 16),
                    'start' => Carbon::create(2021, 4, 16, 12, 0),
                    'end' => Carbon::create(2021, 4, 16, 16, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.16',
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
                serviceCode: ServiceCode::fromString('127049'), // 重訪Ⅲ日中1.0・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 157,
                count: 1,
                totalScore: 157,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127051'), // 重訪Ⅲ日中1.5・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127053'), // 重訪Ⅲ日中2.0・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127055'), // 重訪Ⅲ日中2.5・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127057'), // 重訪Ⅲ日中3.0・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127059'), // 重訪Ⅲ日中3.5・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127061'), // 重訪Ⅲ日中4.0・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('121321'), // 重訪Ⅲ日中8.0
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 8,
                totalScore: 680,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127050'), // 重訪Ⅲ日中1.0・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 157,
                count: 1,
                totalScore: 157,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127052'), // 重訪Ⅲ日中1.5・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127054'), // 重訪Ⅲ日中2.0・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127056'), // 重訪Ⅲ日中2.5・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127058'), // 重訪Ⅲ日中3.0・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127060'), // 重訪Ⅲ日中3.5・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127062'), // 重訪Ⅲ日中4.0・2人・同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 令和3年9月30日までの上乗せ分（重訪）
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 2, // これまでの合計 1924 * 0.1% = 1 (1以下はすべて1)
                count: 1,
                totalScore: 2,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 385, // 合計 1926 * 0.2(20%)
                count: 1,
                totalScore: 385,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 337, // 合計 2311 * 14.6%
                count: 1,
                totalScore: 337,
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
        $I->assertMatchesModelSnapshot($statement->aggregates);
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
                'providedOn' => Carbon::create(2021, 4, 16),
                'serviceCount' => 1,
                'isCoaching' => true,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 8, 0),
                        'end' => Carbon::create(2021, 4, 16, 12, 0),
                    ]),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 8, 0),
                        'end' => Carbon::create(2021, 4, 16, 12, 0),
                    ]),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2021, 4, 16),
                'serviceCount' => 1,
                'isCoaching' => false,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 12, 0),
                        'end' => Carbon::create(2021, 4, 16, 16, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(8_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 12, 0),
                        'end' => Carbon::create(2021, 4, 16, 16, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(8_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'providedOn' => Carbon::create(2021, 4, 16),
                'isCoaching' => true,
                'serviceCount' => 2,
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 8, 0),
                        'end' => Carbon::create(2021, 4, 16, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(4_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 16, 8, 0),
                        'end' => Carbon::create(2021, 4, 16, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(4_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
