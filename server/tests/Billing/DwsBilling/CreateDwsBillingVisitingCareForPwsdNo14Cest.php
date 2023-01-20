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
use Domain\Shift\ServiceOption;
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 重訪 請求テスト.
 * 設定例No.14
 */
final class CreateDwsBillingVisitingCareForPwsdNo14Cest extends CreateDwsBillingTest
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
        $office = $this->examples->offices[2];

        // 予実を準備
        $reportItems = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 14),
                    'start' => Carbon::create(2021, 4, 14, 8, 0),
                    'end' => Carbon::create(2021, 4, 14, 12, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [ServiceOption::coaching()],
                'note' => '設定例No.14',
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
                serviceCode: ServiceCode::fromString('127049'), // 重訪３日中1.0 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 157,
                count: 1,
                totalScore: 157,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127051'), // 重訪３日中1.5 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127053'), // 重訪３日中2.0 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127055'), // 重訪３日中2.5 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127057'), // 重訪３日中3.0 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127059'), // 重訪３日中3.5 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127061'), // 重訪３日中4.0 同行
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),

            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127050'), // 重訪３日中1.0(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 157,
                count: 1,
                totalScore: 157,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127052'), // 重訪３日中1.5(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127054'), // 重訪３日中2.0(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127056'), // 重訪３日中2.5(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127058'), // 重訪３日中3.0(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127060'), // 重訪３日中3.5(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 77,
                count: 1,
                totalScore: 77,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('127062'), // 重訪３日中4.0(2人同行)
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 78,
                count: 1,
                totalScore: 78,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('12ZZ01'), // 重訪 COVID-19 加算
                serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                unitScore: 1, // これまでの合計1244 * 0.1% = 1 (1以下はすべて1)
                count: 1,
                totalScore: 1,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 249, // 合計1245 * 0.2(20%)
                count: 1,
                totalScore: 249,
            ),
            new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                unitScore: 218, // 合計1494 * 14.6%
                count: 1,
                totalScore: 218,
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
                subtotalScore: 1712,
                unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                subtotalFee: 19174,
                unmanagedCopay: 1917,
                managedCopay: 1917,
                cappedCopay: 1917,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 1917,
                subtotalBenefit: 17257,
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

        // サービス提供実績記録票：合計(計画)
        $I->assertNotEmpty($serviceReport->plan);
        $I->assertMatchesJsonSnapshot($serviceReport->plan->toAssoc());
        // サービス提供実績記録票：合計(実績)
        $I->assertNotEmpty($serviceReport->result);
        $I->assertMatchesJsonSnapshot($serviceReport->plan->toAssoc());
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
            'headcount' => 2,
            'isCoaching' => true, // 同行支援
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
                'providedOn' => Carbon::create(2021, 4, 14),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 14, 8, 0),
                        'end' => Carbon::create(2021, 4, 14, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(4_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 4, 14, 8, 0),
                        'end' => Carbon::create(2021, 4, 14, 12, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(4_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
