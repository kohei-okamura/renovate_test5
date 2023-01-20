<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingFile;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Common\TimeRange;
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
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing files Create のテスト。予実から生成する.
 * POST /ltcs-billings
 * PUT /ltcs-billings/{id}/status
 */
class CreateLtcsBillingFileFromProvisionReportCest extends CreateLtcsBillingTest
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

        $office = $this->examples->offices[0];

        // 認証処理
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        // 予実を準備
        $planDates = [
            Carbon::create(2021, 3, 2),
            Carbon::create(2021, 3, 9),
            Carbon::create(2021, 3, 16),
            Carbon::create(2021, 3, 23),
        ];
        $resultDates = [
            Carbon::create(2021, 3, 7),
            Carbon::create(2021, 3, 14),
            Carbon::create(2021, 3, 21),
            Carbon::create(2021, 3, 28),
        ];
        $entries = [
            LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => null,
                'slot' => TimeRange::create(['start' => '05:00', 'end' => '07:00']),
                'timeframe' => Timeframe::midnight(), // NOTE 参考データは「夜」になっているがここはあるべき形で
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
                'note' => 'ISさん 江東区 2021年2月参考',
                'plans' => $planDates,
                'results' => $resultDates,
            ]),
        ];
        $repository = $this->getProvisionReportRepository();
        $repository->store(LtcsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 3),
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
            'fixedAt' => Carbon::create(2021, 4, 11),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        // 請求生成処理
        $I->sendPOST('ltcs-billings', $this->defaultBillingParams());

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $billingFinder = $this->getBillingFinder();
        $findList = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list;
        /** @var \Domain\Billing\LtcsBilling $ltcsBilling */
        $ltcsBilling = $findList->head();

        $billingId = $ltcsBilling->id;

        // 明細書状態確定
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billingId)->values()->flatten();

        $statementRepository = $this->getStatementRepository();
        $statements = $statementRepository->lookupByBundleId(...$bundles->map(fn (LtcsBillingBundle $x): int => $x->id))
            ->values()
            ->flatten();

        Seq::from(...$statements)->take($statements->count() - 1)
            ->each(function (LtcsBillingStatement $x) use ($I, $staff): void {
                $I->sendPut(
                    "/ltcs-billings/{$x->billingId}/bundles/{$x->bundleId}/statements/{$x->id}/status",
                    ['status' => LtcsBillingStatus::fixed()->value()],
                );
                $I->seeResponseCodeIs(HttpCode::OK);
                $I->seeLogCount(1);
                $I->seeLogMessage(0, LogLevel::INFO, '', [
                    'organizationId' => $staff->organizationId,
                    'staffId' => $staff->id,
                ]);
            });
        /** @var \Domain\Billing\LtcsBillingStatement $lastStatement */
        $lastStatement = $statements->takeRight(1)->head();
        $I->sendPut(
            "/ltcs-billings/{$lastStatement->billingId}/bundles/{$lastStatement->bundleId}/statements/{$lastStatement->id}/status",
            ['status' => LtcsBillingStatus::fixed()->value()],
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：明細書が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $lastStatement->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '介護保険サービス：請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $lastStatement->billingId,
        ]);

        // 請求ファイル生成（請求状態確定）
        $I->sendPUT(
            "/ltcs-billings/{$billingId}/status",
            $this->defaultUpdateStatusParam()
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogMessage(4, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 以下JOB内の処理
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '介護保険サービス請求が更新されました', [
            'id' => $billingId,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(5);
        $actual = $I->grabResponseArray();

        $I->sendGET("/ltcs-billings/{$billingId}");
        $latest = $I->grabResponseArray();

        // Responseの検証
        $I->assertEquals(['job', ...array_keys($latest)], array_keys($actual));
        $I->assertModelStrictEquals(
            LtcsBilling::create($latest['billing'])->copy(['files' => null]), // files はJOB内で更新がかかるので検証しない
            LtcsBilling::create($actual['billing'])->copy(['files' => null])
        );
        $I->assertEquals($latest['bundles'], $actual['bundles']);
        $I->assertEquals($latest['statements'], $actual['statements']);

        // 格納データの検証（LtcsBillingFiles）
        $I->assertMatchesModelSnapshot(
            Seq::fromArray($latest['billing']['files'])->map(
                fn (array $x): LtcsBillingFile => LtcsBillingFile::fromAssoc([
                    ...$x,
                    // 型をちゃんと変換する
                    'mimeType' => MimeType::from($x['mimeType']),
                    // 変化する値や JSON に含まれない値は検証から外す
                    'path' => '',
                    // ランダムや時間に依存する部分は検証から外す
                    'token' => '',
                    'createdAt' => Carbon::create(2022, 10, 24, 12, 34, 56),
                ])
            )
        );
    }

    /**
     * 請求生成リクエストパラメータ組み立て.
     *
     * @param array $overwrites
     * @return array
     */
    private function defaultBillingParams(array $overwrites = []): array
    {
        return [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2021-05',
            ...$overwrites,
        ];
    }

    /**
     * 状態変更リクエストパラメータ組み立て.
     *
     * @return array
     */
    private function defaultUpdateStatusParam(): array
    {
        return [
            'status' => LtcsBillingStatus::fixed()->value(),
        ];
    }
}
