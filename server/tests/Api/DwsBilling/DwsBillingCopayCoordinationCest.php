<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsBillingCopayCoordination Download のテスト.
 * GET /dws-billings/{dwsBillingId}/bundles/{dwsBillingBundleId}/copay-coordinations/{id}.pdf
 */
class DwsBillingCopayCoordinationCest extends DwsBillingTest
{
    use ExamplesConsumer;

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
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];

        $I->sendGET("dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}.pdf");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->haveHttpHeader('Content-Type', 'application/pdf');
        $I->seeLogCount(0);
    }

    /**
     * IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when id is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];

        $I->sendGET("dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/id.pdf");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 請求IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenDwsBillingIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when dwsBillingId is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];

        $I->sendGET("dws-billings/id/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$copayCoordination->id}.pdf");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 請求単位IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenDwsBillingBundleIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when dwsBillingBundleId is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];

        $I->sendGET("dws-billings/{$copayCoordination->dwsBillingId}/bundles/dwsBillingBundleId/copay-coordinations/{$copayCoordination->id}.pdf");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者負担上限額管理結果票が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenCopayCoordinationIsNotFound(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when CopayCoordination is not found');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("dws-billings/{$copayCoordination->dwsBillingId}/bundles/{$copayCoordination->dwsBillingBundleId}/copay-coordinations/{$id}.pdf");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingCopayCoordination({$id}) not found");
    }
}
