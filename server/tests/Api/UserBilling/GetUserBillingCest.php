<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserBilling get のテスト
 */
class GetUserBillingCest extends UserBillingTest
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

        $userBilling = $this->examples->userBillings[0];
        $expected = $this->domainToArray(compact('userBilling'));

        $I->sendGET("/user-billings/{$userBilling->id}");

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

        $I->sendGET("/user-billings/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "UserBilling({$id}) not found.");
    }

    /**
     * 他の事業者のUserBillingIdを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfBillingIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if Billing ID not in Organization.');

        $I->actingAs($this->examples->staffs[0]);

        $userBilling = $this->examples->userBillings[3];

        $I->sendGET("/user-billings/{$userBilling->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "UserBilling({$userBilling->id}) not found.");
    }

    /**
     * 権限のないOfficeのUserBillingIdを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfBillingIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if Billing ID not in permitted office.');

        $I->actingAs($this->examples->staffs[28]);

        $userBilling = $this->examples->userBillings[2];

        $I->sendGET("/user-billings/{$userBilling->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "UserBilling({$userBilling->id}) not found.");
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

        $billing = $this->examples->userBillings[0];

        $I->sendGET("/user-billings/{$billing->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
