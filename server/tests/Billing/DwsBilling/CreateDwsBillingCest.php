<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\DwsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsBilling create のテスト.
 * API のエラーのような請求の設定例に依存しないテストケースを担当する.
 * POST /dws-billings
 */
class CreateDwsBillingCest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト
     *
     * @param BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $I->actingAs($this->examples->staffs[0]);

        $officeId = $this->examples->offices[2]->id;
        $item = DwsProvisionReportItem::create([
            'schedule' => Schedule::create([
                'date' => Carbon::create(2021, 2, 1),
                'start' => Carbon::create(2021, 2, 1, 4, 00),
                'end' => Carbon::create(2021, 2, 1, 7, 00),
            ]),
            'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'options' => [],
            'note' => '',
        ]);
        $report = DwsProvisionReport::create([
            'userId' => $this->examples->users[3]->id,
            'officeId' => $officeId,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 2),
            'plans' => [],
            'results' => [$item],
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 3, 11),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
        $report2 = $report->copy([
            'results' => [$item->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 2, 1),
                    'start' => Carbon::create(2021, 2, 1, 13, 00),
                    'end' => Carbon::create(2021, 2, 1, 18, 00),
                ]),
                'category' => DwsProjectServiceCategory::ownExpense(),
            ])],
        ]);

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store($report);
        $repository->store($report2);

        $I->sendPOST(
            'dws-billings',
            $this->createParams(['officeId' => $officeId, 'transactedIn' => '2021-04'])
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

        // 請求
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();

        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $bundles->head();

        // サービス実績記録票 に 自費 が含まれていないことを確認する
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($bundle->id)->head()[1];
        $I->assertCount(1, $serviceReports);
        /** @var \Domain\Billing\DwsBillingServiceReport $serviceReport */
        $serviceReport = $serviceReports->head();
        $items = $serviceReport->items;
        $I->assertCount(1, $items);
        $I->assertEquals(DwsGrantedServiceCode::visitingCareForPwsd3(), $items[0]->serviceType);
    }

    /**
     * 事業所が存在しない場合に400が返るテスト.
     *
     * @param BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenOfficeDoesNotExist(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when office does not exist.');

        $I->actingAs($this->examples->staffs[0]);

        $I->sendPOST('dws-billings', $this->createParams(['officeId' => self::NOT_EXISTING_ID]));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 請求対象となる予実が存在しない場合に400が返るテスト.
     *
     * @param BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenDwsProvisionReportDoesNotExist(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when DWS provision report does not exist.');

        $I->actingAs($this->examples->staffs[0]);

        $item = DwsProvisionReportItem::create([
            'schedule' => Schedule::create([
                'date' => Carbon::create(2021, 10, 1),
                'start' => Carbon::create(2021, 10, 1, 4, 00),
                'end' => Carbon::create(2021, 10, 1, 7, 00),
            ]),
            'category' => DwsProjectServiceCategory::ownExpense(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'options' => [],
            'note' => '',
        ]);
        $report = DwsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $this->examples->offices[0]->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 10),
            'plans' => [],
            'results' => [$item],
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 11, 11),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store($report);

        $I->sendPOST('dws-billings', $this->createParams(['transactedIn' => '2021-12']));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['対象となる予実が存在しません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 処理対象年月が不正な日付フォーマットの場合に400が返るテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenTransactedInIsInvalidFormat(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when transactedIn is invalid format.');

        $I->actingAs($this->examples->staffs[0]);

        $I->sendPOST('dws-billings', $this->createParams(['transactedIn' => '2020-13']));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['transactedIn' => ['正しい日付を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failedWithForbiddenWhenNoPermission(BillingTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $billing = $this->examples->dwsBillings[0];

        $I->sendPOST('dws-billings', $this->createParams());

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータの組み立て.
     *
     * @param array $overwrites
     * @return array
     */
    private function createParams(array $overwrites = []): array
    {
        return $overwrites + [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2021-10',
        ];
    }
}
