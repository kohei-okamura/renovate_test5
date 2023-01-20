<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Billing\LtcsBillingFinder;
use Domain\Billing\LtcsBillingInvoiceRepository;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementFinder;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Job\JobStatus;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Psr\Log\LogLevel;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Ltcs Statement refresh のテスト.
 * POST /ltcs-billings/{billingId}/refresh-statement
 */
class RefreshLtcsStatementCest extends Test
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[0];

        // 予実を準備
        $planDates = [
            Carbon::create(2021, 2, 2),
            Carbon::create(2021, 2, 9),
            Carbon::create(2021, 2, 16),
            Carbon::create(2021, 2, 23),
        ];
        $resultDates = [
            Carbon::create(2021, 2, 7),
            Carbon::create(2021, 2, 14),
            Carbon::create(2021, 2, 21),
            Carbon::create(2021, 2, 28),
        ];
        $entries = $this->entries($planDates, $resultDates);
        $provisionReportRepository = $this->getProvisionReportRepository();
        $storedProvisionReport = $provisionReportRepository->store(LtcsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 2),
            'entries' => $entries,
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(1995, 11, 9),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST(
            'ltcs-billings',
            [
                'officeId' => $this->examples->offices[0]->id,
                'transactedIn' => '1995-11',
            ]
        );

        $I->seeLogCount(3);
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 予実を更新
        $provisionReportRepository->store($storedProvisionReport->copy([
            'entries' => $this->entries(
                [Carbon::create(2021, 2, 2)],
                [Carbon::create(2021, 2, 10)]
            ),
        ]));

        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $billingFinder
            ->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])
            ->list
            ->head();
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\LtcsBillingBundle $bundle */
        $bundle = $bundleRepository->lookupByBillingId($billing->id)->head()[1]->head();
        $ids = $this->getStatementFinder()
            ->find(['billingId' => $billing->id], ['sortBy' => 'id', 'all' => true])
            ->list
            ->map(fn (LtcsBillingStatement $x): int => $x->id)
            ->toArray();

        // 介護保険サービス明細書を最新の予実を使って更新（再生成）
        $I->sendPost(
            "/ltcs-billings/{$billing->id}/statement-refresh",
            compact('ids')
        );

        $I->seeLogCount(5);
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogMessage(4, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '介護保険サービス：請求単位が更新されました', [
            'id' => $bundle->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '介護保険サービス：明細書が更新されました', [
            'id' => implode(',', $ids),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        // TODO: 値の検証をする
    }

    /**
     * 介護保険サービス：予実リポジトリを取得する.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportRepository
     */
    private function getProvisionReportRepository(): LtcsProvisionReportRepository
    {
        return app(LtcsProvisionReportRepository::class);
    }

    /**
     * 介護保険サービス：請求単位リポジトリを取得する.
     *
     * @return \Domain\Billing\LtcsBillingBundleRepository
     */
    private function getBundleRepository(): LtcsBillingBundleRepository
    {
        return app(LtcsBillingBundleRepository::class);
    }

    /**
     * 介護保険サービス：請求書リポジトリを取得する.
     *
     * @return \Domain\Billing\LtcsBillingInvoiceRepository
     */
    private function getInvoiceRepository(): LtcsBillingInvoiceRepository
    {
        return app(LtcsBillingInvoiceRepository::class);
    }

    /**
     * 介護保険サービス：明細書リポジトリを取得する.
     *
     * @return \Domain\Billing\LtcsBillingStatementRepository
     */
    private function getStatementRepository(): LtcsBillingStatementRepository
    {
        return app(LtcsBillingStatementRepository::class);
    }

    /**
     * 介護保険サービス：請求 Finder を取得する.
     *
     * @return \Domain\Billing\LtcsBillingFinder
     */
    private function getBillingFinder(): LtcsBillingFinder
    {
        return app(LtcsBillingFinder::class);
    }

    /**
     * 介護保険サービス：明細書 Finder を取得する.
     *
     * @return \Domain\Billing\LtcsBillingStatementFinder
     */
    private function getStatementFinder(): LtcsBillingStatementFinder
    {
        return app(LtcsBillingStatementFinder::class);
    }

    /**
     * 予実サービス情報.
     *
     * @param array|\Domain\Common\Carbon[] $planDates
     * @param array|\Domain\Common\Carbon[] $resultDates
     * @return array
     */
    private function entries(array $planDates, array $resultDates): array
    {
        return [
            LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => null,
                'slot' => TimeRange::create(['start' => '05:00', 'end' => '07:00']),
                'timeframe' => Timeframe::morning(),
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'amounts' => [
                    LtcsProjectAmount::create([
                        'category' => LtcsProjectAmountCategory::physicalCare(),
                        'amount' => 120,
                    ]),
                ],
                'headcount' => 1,
                'serviceCode' => ServiceCode::fromString('111412'), // カイポケより 身体介護4・夜
                'options' => [],
                'note' => 'ISさん 江東区',
                'plans' => $planDates,
                'results' => $resultDates,
            ]),
        ];
    }
}
