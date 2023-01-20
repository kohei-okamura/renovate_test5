<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing Copay Coordination Status Update のテスト（Dws）
 * PUT /dws-billings/{dwsBillingId}/bundles/{dwsBillingBundleId}/copay-coordinations/{dwsBillingCopayCoordinationId}/status
 */
class UpdateDwsBillingCopayCoordinationStatusCest extends Test
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[7];

        $I->sendPUT(
            "/dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam(DwsBillingStatus::fixed())
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(2);
        // 明細書も更新するためログが出力される
        // そうなるようにデータを用意しているので、ID はべた書き
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：明細書が更新されました', [
            'id' => 20,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '利用者負担上限額管理結果票が更新されました', [
            'id' => $copayCoordination->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$id}/status",
            $this->defaultParam($copayCoordination)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingCopayCoordination({$id}) not found.");
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];
        $billingId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam($copayCoordination)
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];
        $billingBundleId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "/dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$billingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam($copayCoordination)
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];
        $billingId = $this->examples->dwsBillings[3]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam($copayCoordination)
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];
        $billingId = $this->examples->dwsBillings[1]->id;

        $I->sendPUT(
            "/dws-billings/{$billingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam($copayCoordination)
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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[6];

        $I->sendPUT(
            "/dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}/status",
            $this->defaultParam($copayCoordination)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param \Domain\Billing\DwsBillingCopayCoordination|\Domain\Billing\DwsBillingStatus $param
     * @return array
     */
    private function defaultParam($param): array
    {
        if ($param instanceof DwsBillingCopayCoordination) {
            $status = $param->status;
        } else {
            $status = $param;
        }

        return ['status' => $status->value()];
    }
}
