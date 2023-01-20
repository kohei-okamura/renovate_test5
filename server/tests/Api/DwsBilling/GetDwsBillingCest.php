<?php
/**
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
 * DwsBilling get のテスト.
 * GET /dws-billings/{id}
 */
class GetDwsBillingCest extends DwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $I->actingAs($this->examples->staffs[0]);

        $billing = $this->examples->dwsBillings[0];
        $bundles = [$this->examples->dwsBillingBundles[1]];
        $copayCoordinations = [$this->examples->dwsBillingCopayCoordinations[2]];
        $reports = [$this->examples->dwsBillingServiceReports[2]];
        $statements = [$this->examples->dwsBillingStatements[2]];
        $expected = $this->domainToArray(compact(
            'billing',
            'bundles',
            'copayCoordinations',
            'reports',
            'statements'
        ));

        $I->sendGET("/dws-billings/{$billing->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfIdNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if id not exist.');

        $id = self::NOT_EXISTING_ID;

        $I->actingAs($this->examples->staffs[0]);

        $I->sendGET("/dws-billings/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$id}) not found.");
    }

    /**
     * 他の事業者のBillingIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfBillingIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if Billing ID not in Organization.');

        $I->actingAs($this->examples->staffs[0]);

        $billing = $this->examples->dwsBillings[3];

        $I->sendGET("/dws-billings/{$billing->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billing->id}) not found.");
    }

    /**
     * 権限のないOfficeのBillingIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfBillingIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if Billing ID not in permitted office.');

        $I->actingAs($this->examples->staffs[28]);

        $billing = $this->examples->dwsBillings[1];

        $I->sendGET("/dws-billings/{$billing->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billing->id}) not found.");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $billing = $this->examples->dwsBillings[0];

        $I->sendGET("/dws-billings/{$billing->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
