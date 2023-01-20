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
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス重訪請求テスト.
 * 連続時間で日跨ぎするパターン
 */
final class CreateDwsBillingVisitingCareForPwsdHeadcount2OverDaysCest extends CreateDwsBillingTest
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
                    'date' => Carbon::create(2021, 5, 20),
                    'start' => Carbon::create(2021, 5, 20, 19, 0),
                    'end' => Carbon::create(2021, 5, 21, 9, 0),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 2,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 5, 21),
                    'start' => Carbon::create(2021, 5, 21, 9, 0),
                    'end' => Carbon::create(2021, 5, 21, 20, 0),
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
            'providedIn' => Carbon::create(2021, 5),
            'plans' => $reportItems,
            'results' => $reportItems,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 6, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', ['transactedIn' => '2021-06', 'officeId' => $office->id]);

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
//        $this->checkStatementItems($I, $actualBundle);
//        $this->checkStatementAggregates($I, $actualBundle);
        $this->checkServiceReportItems($I, $actualBundle);
        $this->checkServiceReportAggregates($I, $actualBundle);
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
            DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(39_0000),
            ],
            DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(0),
            ],
        ]);
        // サービス提供実績記録票：合計(計画)
        $I->assertNotEmpty($serviceReport->plan);
        $I->assertEquals($expectAggregate, $serviceReport->plan); // TODO
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
            'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
            'providerType' => DwsBillingServiceReportProviderType::none(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'serialNumber' => 1,
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
                'headcount' => 2,
                'serviceCount' => 0,
                'providedOn' => Carbon::create(2021, 5, 20),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 20, 19, 0),
                        'end' => Carbon::create(2021, 5, 21, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(5_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 20, 19, 0),
                        'end' => Carbon::create(2021, 5, 21, 0, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(5_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 2,
                'serviceCount' => 1,
                'providedOn' => Carbon::create(2021, 5, 21),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 21, 0, 0),
                        'end' => Carbon::create(2021, 5, 21, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(20_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 21, 0, 0),
                        'end' => Carbon::create(2021, 5, 21, 20, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(20_0000),
                ]),
            ]),
            $baseItem->copy([
                'serialNumber' => 3,
                'serviceCount' => 2,
                'providedOn' => Carbon::create(2021, 5, 21),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 21, 0, 0),
                        'end' => Carbon::create(2021, 5, 21, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(9_0000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::create(2021, 5, 21, 0, 0),
                        'end' => Carbon::create(2021, 5, 21, 9, 0),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(9_0000),
                ]),
            ]),
        ], $serviceReport->items);
    }
}
