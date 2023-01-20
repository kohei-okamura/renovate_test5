<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingStatus;
use Domain\Job\JobStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Bulk Service Report Status update のテスト(Dws).
 * POST /dws-billings/{billingId}/service-report-status-update
 */
class BulkUpdateServiceReportStatusCest extends Test
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

        $serviceReports = $this->examples->dwsBillingServiceReports;
        $billingId = $serviceReports[7]->dwsBillingId;
        $ids = [
            $serviceReports[7]->id,
            $serviceReports[8]->id,
        ];

        $I->sendPost(
            "/dws-billings/{$billingId}/service-report-status-update",
            $this->params($ids)
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        // ログ確認
        $I->seeLogCount(5);
        $I->seeLogMessage(4, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'サービス提供実績記録票が更新されました', [
            'id' => implode(',', $ids),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, '障害福祉サービス：請求が更新されました', [
            'id' => $billingId,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $serviceReports = $this->examples->dwsBillingServiceReports;
        $billingId = $serviceReports[7]->dwsBillingId;
        $ids = [
            $serviceReports[7]->id,
            $serviceReports[8]->id,
        ];

        $I->sendPost(
            "/dws-billings/{$billingId}/service-report-status-update",
            $this->params($ids)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータ.
     *
     * @param array $ids
     * @return array
     */
    private function params(array $ids): array
    {
        return [
            'ids' => $ids,
            'status' => DwsBillingStatus::fixed()->value(),
        ];
    }
}
