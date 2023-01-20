<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Statement Status Update のテスト(Dws).
 * PUT /dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/status
 */
class UpdateStatementStatusCest extends Test
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
        $statement = $this->examples->dwsBillingStatements[6];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam(DwsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(2);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'id' => $statement->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '障害福祉サービス：請求が更新されました', [
            'id' => $statement->dwsBillingId,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 「上限管理区分」が「未作成」のときに更新ができないテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsUncreated(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when copayCoordinationStatus is uncreated');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[11];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam(DwsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['status' => ['利用者負担上限額管理結果結果票が未入力のため状態を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 「上限管理区分」が「未入力」のときに更新ができないテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenCopayCoordinationStatusIsUnfilled(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when copayCoordinationStatus is unfilled');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $statement = $this->examples->dwsBillingStatements[12];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam(DwsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['status' => ['利用者負担上限額管理結果結果票が未入力のため状態を更新できません。']]]);
        $I->seeLogCount(0);
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
        $statement = $this->examples->dwsBillingStatements[6];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$id}/status",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingStatement({$id}) not found.");
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam($statement)
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingBundleId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$billingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam($statement)
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = $this->examples->dwsBillings[3]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam($statement)
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
        $statement = $this->examples->dwsBillingStatements[6];
        $billingId = $this->examples->dwsBillings[1]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam($statement)
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
        $statement = $this->examples->dwsBillingStatements[6];

        $I->sendPUT(
            "/dws-billings/{$statement->dwsBillingId}/bundles/{$statement->dwsBillingBundleId}/statements/{$statement->id}/status",
            $this->defaultParam($statement)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param \Domain\Billing\DwsBillingStatement|\Domain\Billing\DwsBillingStatus $param
     * @return array
     */
    private function defaultParam($param): array
    {
        if ($param instanceof DwsBillingStatement) {
            $status = $param->status;
        } else {
            $status = $param;
        }

        return ['status' => $status->value()];
    }
}
