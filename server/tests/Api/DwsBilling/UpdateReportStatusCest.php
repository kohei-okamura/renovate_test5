<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Report status Update のテスト(Dws).
 * PUT /dws-billings/{billingId}/bundles/{billingBundleId}/reports/{id}/status
 */
class UpdateReportStatusCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $report = $this->serviceReport();

        $I->sendPUT(
            "/dws-billings/{$report->dwsBillingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}/status",
            $this->defaultParam(DwsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：サービス実績記録票が更新されました', [
            'id' => $report->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '障害福祉サービス：請求が更新されました', [
            'id' => $report->dwsBillingId,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        // Responseの検証
        $I->sendGET("/dws-billings/{$report->dwsBillingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}");
        $latest = $I->grabResponseArray();

        $I->assertEquals(
            tap($latest, function (&$x) {
                $x['billing']['status'] = DwsBillingStatus::ready()->value();
                $x['billing']['updatedAt'] = null;
            }),
            tap($actual, function (&$x) {
                $x['billing']['updatedAt'] = null;
            }),
        );
    }

    /**
     * IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $report = $this->serviceReport();
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$report->dwsBillingId}/bundles/{$report->dwsBillingBundleId}/reports/{$id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingServiceReport({$id}) not found.");
    }

    /**
     * 請求IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $report = $this->serviceReport();
        $billingId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
    }

    /**
     * 請求単位IDが存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingBundleIdNotExists(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when BillingBundle ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $report = $this->serviceReport();
        $billingBundleId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$report->dwsBillingId}/bundles/{$billingBundleId}/reports/{$report->id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingBundle({$billingBundleId}) not found");
    }

    /**
     * 請求IDが同じ事業者に存在しない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID not in Organization.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $report = $this->serviceReport();
        $billingId = $this->examples->dwsBillings[3]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
    }

    /**
     * アクセス可能なOfficeの請求IDでない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenBillingIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Billing ID is not in accessible Office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $report = $this->serviceReport();
        $billingId = $this->examples->dwsBillings[1]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithForbiddenWhenNotHavePermission(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when not have Permission.');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $report = $this->serviceReport();

        $I->sendPUT(
            "/dws-billings/{$report->dwsBillingId}/bundles/{$report->dwsBillingBundleId}/reports/{$report->id}/status",
            $this->defaultParam($report)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param \Domain\Billing\DwsBillingServiceReport|\Domain\Billing\DwsBillingStatus $param
     * @return array
     */
    private function defaultParam($param): array
    {
        if ($param instanceof DwsBillingServiceReport) {
            $status = $param->status;
        } else {
            $status = $param;
        }

        return ['status' => $status->value()];
    }

    /**
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    private function serviceReport(): DwsBillingServiceReport
    {
        return $this->examples->dwsBillingServiceReports[0]->copy(['status' => DwsBillingStatus::ready()]);
    }
}
