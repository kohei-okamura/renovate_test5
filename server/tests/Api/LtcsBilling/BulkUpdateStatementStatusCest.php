<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBillingStatus;
use Domain\Job\JobStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Bulk Statement Status update のテスト(Ltcs).
 * POST /ltcs-billings/{billingId}/bundles/{bundleId}/statements/bulk-status
 */
class BulkUpdateStatementStatusCest extends Test
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
        $billingId = $this->examples->ltcsBillingStatements[5]->billingId;
        $bundleId = $this->examples->ltcsBillingStatements[5]->bundleId;
        $ids = [
            $this->examples->ltcsBillingStatements[5]->id,
            $this->examples->ltcsBillingStatements[6]->id,
        ];

        $I->sendPost(
            "/ltcs-billings/{$billingId}/bundles/{$bundleId}/statements/bulk-status",
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
        $I->seeLogMessage(1, LogLevel::INFO, '介護保険サービス：明細書が更新されました', [
            'id' => '',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(3, LogLevel::INFO, '介護保険サービス：請求が更新されました', [
            'id' => $billingId,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
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
        $billingId = $this->examples->ltcsBillingStatements[5]->billingId;
        $bundleId = $this->examples->ltcsBillingStatements[5]->bundleId;
        $ids = [
            $this->examples->ltcsBillingStatements[5]->id,
            $this->examples->ltcsBillingStatements[6]->id,
        ];

        $I->sendPost(
            "/ltcs-billings/{$billingId}/bundles/{$bundleId}/statements/bulk-status",
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
            'status' => LtcsBillingStatus::fixed(),
        ];
    }
}
