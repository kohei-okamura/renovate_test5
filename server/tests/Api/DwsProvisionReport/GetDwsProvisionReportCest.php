<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport get のテスト.
 * GET /dws-provision-reports/{officeId}/{userId}/{providedIn}
 */
class GetDwsProvisionReportCest extends DwsProvisionReportTest
{
    use ExamplesConsumer;

    // tests

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];
        $expected = $this->domainToArray(compact('dwsProvisionReport'));

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenTargetContractIsTheMiddleOfTheMonth(ApiTester $I)
    {
        $I->wantTo('succeed api call when target contract is the middle of the month');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[6];
        $expected = $this->domainToArray(compact('dwsProvisionReport'));

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 事業所IDが一致しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenTheDataDoesNotMatchOfficeId(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when the data does not match officeId.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = self::NOT_EXISTING_ID;
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$officeId}] is not found");
    }

    /**
     * 事業所IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenOfficeIdIsString(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when officeId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = 'error';
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが一致しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenTheDataDoesNotMatchUserId(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when the data does not match userId.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = self::NOT_EXISTING_ID;
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 利用者IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenUserIdIsString(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when userId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = 'error';
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が一致しない場合に204が返るテスト.
     *
     * @param ApiTester $I
     */
    public function succeedWithNoContentWhenTheDataDoesNotMatchProvidedIn(ApiTester $I)
    {
        $I->wantTo('succeed with NoContent when the data does not match providedIn.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-12';
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}");

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が不正な日付フォーマットの場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenProvidedInIsInvalidFormat(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when providedIn is invalid format.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-13';
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能な事業所でない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[5];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$dwsProvisionReport->officeId}] is not found");
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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
