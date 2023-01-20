<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingFile;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing Status Update のテスト（Dws）
 * PUT /dws-billings/{id}/status
 */
final class UpdateDwsBillingStatusCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    private const DATA_TYPE_INVOICE = 'J11';
    private const DATA_TYPE_COPAY_COORDINATION = 'J41';
    private const DATA_TYPE_SERVICE_REPORT = 'J61';

    private const RECORD_CATEGORY_INVOICE = 'J111';

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICall(ApiTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[0]->id;

        // 請求に紐づく明細書と実績記録票を全て確定にしておく

        /**
         * @var \Domain\Billing\DwsBillingBundleRepository $bundleRepository
         * @var \Domain\Billing\DwsBillingStatementRepository $statmentRepository
         * @var \Domain\Billing\DwsBillingServiceReportRepository $serviceReportRepository
         * @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
         * @var int[] $bundleIds
         * @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements
         * @var \Domain\Billing\DwsBillingServiceReport[]|\ScalikePHP\Seq $serviceReports
         */
        $bundleRepository = app(DwsBillingBundleRepository::class);
        $statementRepository = app(DwsBillingStatementRepository::class);
        $serviceReportRepository = app(DwsBillingServiceReportRepository::class);
        $bundles = $bundleRepository->lookupByBillingId($id)->values()->flatten();
        $bundleIds = $bundles->map(fn (DwsBillingBundle $x): int => $x->id);
        $statements = $statementRepository
            ->lookupByBundleId(...$bundles->map(fn (DwsBillingBundle $x): int => $x->id))
            ->values()
            ->flatten();
        $serviceReports = $serviceReportRepository
            ->lookupByBundleId(...$bundleIds)
            ->values()
            ->flatten();

        $statements->each(
            fn (DwsBillingStatement $x): DwsBillingStatement => $statementRepository->store($x->copy([
                'status' => DwsBillingStatus::fixed(),
            ]))
        );
        $serviceReports->each(
            fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $serviceReportRepository->store($x->copy([
                'status' => DwsBillingStatus::fixed(),
            ]))
        );

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：請求が更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(4, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 以下JOB内の処理
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '障害福祉サービス請求が更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogCount(5);
        $actual = $I->grabResponseArray();

        $I->sendGET("/dws-billings/{$id}");
        $latest = $I->grabResponseArray();

        // Responseの検証
        $I->assertEquals([...array_keys($latest), 'job'], array_keys($actual));
        $I->assertModelStrictEquals(
            DwsBilling::create($latest['billing'])->copy(['files' => null]), // files はJOB内で更新がかかるので検証しない
            DwsBilling::create($actual['billing'])->copy(['files' => null])
        );
        $I->assertEquals($latest['bundles'], $actual['bundles']);
        $I->assertEquals($latest['copayCoordinations'], $actual['copayCoordinations']);
        $I->assertEquals($latest['reports'], $actual['reports']);
        $I->assertEquals($latest['statements'], $actual['statements']);

        // 格納データの検証（DwsBillingFiles）
        $I->assertMatchesModelSnapshot(
            Seq::fromArray($latest['billing']['files'])->map(
                fn (array $x): DwsBillingFile => DwsBillingFile::fromAssoc([
                    ...$x,
                    'mimeType' => MimeType::from($x['mimeType']),
                    'downloadedAt' => Carbon::parseOption($x['downloadedAt'])->orNull(),
                    // JSON に含まれない値・変化する値は検証から外す（ダミーの値を指定する）
                    'path' => 'attachments/xyz.csv',
                    'token' => str_repeat('x', 60),
                    'createdAt' => Carbon::create(2022, 5, 12, 18, 13, 51),
                ])
            )
        );

        // ファイル数の検証
        // このテストで使用している billingId に紐づいている請求単位のサービス提供年月には「2020年10月」と「2020年11月」があるため、
        // （CSV x 3, pdf x 3） x 2 のファイルが作られているはず
        $repository = $this->getBillingRepository();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $repository->lookup($actual['billing']['id'])->head();
        $groupedFiles = Seq::fromArray($billing->files)
            ->groupBy(fn (DwsBillingFile $x): string => $x->mimeType->value());
        $I->assertSame(6, $groupedFiles->get(MimeType::csv()->value())->toSeq()->flatten()->size());
        $I->assertSame(6, $groupedFiles->get(MimeType::pdf()->value())->toSeq()->flatten()->size());
    }

    /**
     * 無効に設定できるテスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICallWithDisabled(ApiTester $I): void
    {
        $I->wantTo('succeed API call with Disabled');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[6]->id;

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            ['status' => DwsBillingStatus::disabled()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：請求が更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->sendGet("/dws-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $response = $I->grabResponseArray();
        $I->assertSame(DwsBillingStatus::disabled()->value(), $response['billing']['status']);
    }

    /**
     * IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenIdNotExists(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$id}) not found.");
    }

    /**
     * 請求IDが同じ事業者に存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBillingIdNotInOrganization(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when Billing ID not in Organization.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[3]->id;

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$id}) not found.");
    }

    /**
     * アクセス可能なOfficeの請求IDでない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithNotFoundWhenBillingIdIsNotInAccessibleOffice(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when Billing ID is not in accessible Office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[1]->id;

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$id}) not found.");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \ApiTester $I
     * @noinspection PhpUnused
     */
    public function failWithForbiddenWhenNotHavePermission(ApiTester $I): void
    {
        $I->wantTo('fail with NotFound when not have Permission.');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[0]->id;

        $I->sendPUT(
            "/dws-billings/{$id}/status",
            $this->defaultParam()
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return [
            'status' => DwsBillingStatus::fixed()->value(),
        ];
    }

    /**
     * 請求Repositoryの取得.
     *
     * @return \Domain\Billing\DwsBillingRepository
     */
    private function getBillingRepository(): DwsBillingRepository
    {
        return app(DwsBillingRepository::class);
    }
}
