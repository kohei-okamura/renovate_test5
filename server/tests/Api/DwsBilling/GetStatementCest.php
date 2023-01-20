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
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Statement get のテスト(Dws).
 * GET /dws-billings/{billingId}/bundles/{bundleId}/statements/{id}
 */
class GetStatementCest extends Test
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
        $bundle = $this->examples->dwsBillingBundles[5];
        $statement = $this->examples->dwsBillingStatements[6];
        $expected = $this->domainToArray(compact('billing', 'bundle', 'statement'));

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

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

        $billing = $this->examples->dwsBillings[0];
        $bundle = $this->examples->dwsBillingBundles[1];

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingStatement({$id}) not found.");
    }

    /**
     * 存在しない請求IDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfBillingIdNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if Billing ID not exist.');

        $I->actingAs($this->examples->staffs[0]);

        $billingId = self::NOT_EXISTING_ID;
        $bundle = $this->examples->dwsBillingBundles[1];
        $statement = $this->examples->dwsBillingStatements[2];

        $I->sendGET("/dws-billings/{$billingId}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBilling({$billingId}) not found.");
    }

    /**
     * 存在しないBundleIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfBundleIdNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if Bundle ID not exist.');

        $I->actingAs($this->examples->staffs[0]);

        $billing = $this->examples->dwsBillings[0];
        $bundleId = self::NOT_EXISTING_ID;
        $statement = $this->examples->dwsBillingStatements[2];

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundleId}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsBillingBundle({$bundleId}) not found.");
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
        // 以下は厳密にはリレーションがないが、EnsureUseCase で弾かれるため、リレーションがなくても問題ないこととする
        $bundle = $this->examples->dwsBillingBundles[1];
        $statement = $this->examples->dwsBillingStatements[2];

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

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
        // 以下は厳密にはリレーションがないが、EnsureUseCase で弾かれるため、リレーションがなくても問題ないこととする
        $bundle = $this->examples->dwsBillingBundles[1];
        $statement = $this->examples->dwsBillingStatements[2];

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

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
        $bundle = $this->examples->dwsBillingBundles[1];
        $statement = $this->examples->dwsBillingStatements[2];

        $I->sendGET("/dws-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
