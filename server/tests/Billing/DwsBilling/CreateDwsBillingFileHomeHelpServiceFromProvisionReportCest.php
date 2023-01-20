<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\DwsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingFile;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing files Create (居宅) のテスト. 予実から生成する.
 */
final class CreateDwsBillingFileHomeHelpServiceFromProvisionReportCest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICall(BillingTester $I): void
    {
        $I->wantTo('succeed API Call with multiple free time.');

        $office = $this->examples->offices[0];

        // ////////
        // 認証処理
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        // ////////
        // 予実を準備
        $baseItem = DwsProvisionReportItem::create([
            'category' => DwsProjectServiceCategory::physicalCare(),
            'headcount' => 1,
            'movingDurationMinutes' => 0,
            'options' => [],
            'note' => '設定例No.1',
        ]);
        $reportResults = [
            $baseItem->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 3),
                    'start' => Carbon::create(2021, 4, 3, 10, 0),
                    'end' => Carbon::create(2021, 4, 3, 11, 30),
                ]),
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', ['officeId' => $office->id, 'transactedIn' => '2021-05']);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();
        $billingId = $billing->id;
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billingId)->values()->flatten();

        /** @var int[] $bundleIds */
        $bundleIds = $bundles->map(fn (DwsBillingBundle $x): int => $x->id);

        $statementRepository = $this->getStatementRepository();
        $statements = $statementRepository->lookupByBundleId(...$bundleIds)
            ->values()
            ->flatten();

        // ////////
        // 上限管理結果登録
        // 上限管理なしは弾く
        $statements
            ->filter(function (DwsBillingStatement $x): bool {
                return $x->copayCoordinationStatus !== DwsBillingStatementCopayCoordinationStatus::unapplicable();
            })
            ->each(function (DwsBillingStatement $x) use ($I): void {
                $I->sendPut(
                    "/dws-billings/{$x->dwsBillingId}/bundles/{$x->dwsBillingBundleId}/statements/{$x->id}/copay-coordination",
                    [
                        'result' => CopayCoordinationResult::notCoordinated()->value(),
                        'amount' => 0,
                    ]
                );
                $I->seeResponseCodeIs(HttpCode::OK);
                $I->seeLogCount(1);
            });

        // ////////
        // 実績記録票状態確定
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId(...$bundleIds)
            ->values()
            ->flatten();
        /** @var \Domain\Billing\DwsBillingServiceReport $lastServiceReport */
        $lastServiceReport = $serviceReports->takeRight(1)->head();
        $I->sendPost(
            "/dws-billings/{$lastServiceReport->dwsBillingId}/service-report-status-update",
            [
                'ids' => $serviceReports->map(fn (DwsBillingServiceReport $x): int => $x->id)->toArray(),
                'status' => DwsBillingStatus::fixed()->value(),
            ],
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(4);

        // ////////
        // 明細書状態確定
        /** @var \Domain\Billing\DwsBillingStatement $lastStatement */
        $lastStatement = $statements->takeRight(1)->head();
        $I->sendPost(
            "/dws-billings/{$lastStatement->dwsBillingId}/statement-status-update",
            [
                'ids' => $statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray(),
                'status' => DwsBillingStatus::fixed()->value(),
            ],
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(5);

        // ////////
        // 請求ファイル生成（請求状態確定）
        $I->sendPUT(
            "/dws-billings/{$billingId}/status",
            ['status' => DwsBillingStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(5);
        $actual = $I->grabResponseArray();

        $I->sendGET("/dws-billings/{$billingId}");
        $latest = $I->grabResponseArray();

        // ////////
        // Responseの検証
        $I->assertEquals([...array_keys($latest), 'job'], array_keys($actual));
        $I->assertModelStrictEquals(
            DwsBilling::create($latest['billing'])->copy(['files' => null]), // files はJOB内で更新がかかるので検証しない
            DwsBilling::create($actual['billing'])->copy(['files' => null])
        );
        $I->assertEquals($latest['bundles'], $actual['bundles']);
        $I->assertEquals($latest['statements'], $actual['statements']);

        // 格納データの検証（DwsBillingFiles）
        $I->assertCount(4, $latest['billing']['files'], 'Number of files');
        $I->assertMatchesModelSnapshot(
            Seq::fromArray($latest['billing']['files'])->map(
                fn (array $x): DwsBillingFile => DwsBillingFile::fromAssoc([
                    ...$x,
                    'mimeType' => MimeType::from($x['mimeType']),
                    'downloadedAt' => Carbon::parseOption($x['downloadedAt'])->orNull(),
                    // JSON に含まれない値・変化する値は検証から外す（ダミーの値を指定する）
                    'path' => 'attachments/xyz.csv',
                    'token' => str_repeat('x', 60),
                    'createdAt' => Carbon::create(2022, 1, 23, 1, 23, 45),
                ])
            )
        );
        // TODO ファイルの検証
    }
}
