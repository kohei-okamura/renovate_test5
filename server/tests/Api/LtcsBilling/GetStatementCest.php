<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Statement getのテスト（Ltcs）.
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

        $billing = $this->examples->ltcsBillings[0];
        $bundle = $this->examples->ltcsBillingBundles[0];
        $statement = $this->examples->ltcsBillingStatements[2];
        $expected = $this->domainToArray(compact('billing', 'bundle', 'statement'));

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
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

        $billing = $this->examples->ltcsBillings[0];
        $bundle = $this->examples->ltcsBillingBundles[0];

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingStatement({$id}) not found.");
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
        $bundle = $this->examples->ltcsBillingBundles[0];
        $statement = $this->examples->ltcsBillingStatements[2];

        $I->sendGET("/ltcs-billings/{$billingId}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$billingId}) not found.");
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

        $billing = $this->examples->ltcsBillings[0];
        $bundleId = self::NOT_EXISTING_ID;
        $statement = $this->examples->ltcsBillingStatements[2];

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundleId}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBillingBundle({$bundleId}) not found.");
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

        $billing = $this->examples->ltcsBillings[4];
        // 以下は厳密にはリレーションがないが、EnsureUseCase で弾かれるため、リレーションがなくても問題ないこととする
        $bundle = $this->examples->ltcsBillingBundles[0];
        $statement = $this->examples->ltcsBillingStatements[2];

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$billing->id}) not found.");
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

        $billing = $this->examples->ltcsBillings[5];
        // 以下は厳密にはリレーションがないが、EnsureUseCase で弾かれるため、リレーションがなくても問題ないこととする
        $bundle = $this->examples->ltcsBillingBundles[0];
        $statement = $this->examples->ltcsBillingStatements[2];

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsBilling({$billing->id}) not found.");
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

        $billing = $this->examples->ltcsBillings[0];
        $bundle = $this->examples->ltcsBillingBundles[0];
        $statement = $this->examples->ltcsBillingStatements[2];

        $I->sendGET("/ltcs-billings/{$billing->id}/bundles/{$bundle->id}/statements/{$statement->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
