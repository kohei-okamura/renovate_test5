<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * ProvisionReport get のテスト.
 * GET /ltcs-provision-reports/{officeId}/{userId}/{providedIn}
 */
class GetLtcsProvisionReportCest extends LtcsProvisionReportTest
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];
        $expected = $this->domainToArray(compact('ltcsProvisionReport'));

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];
        $expected = $this->domainToArray(compact('ltcsProvisionReport'));

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が一致しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function succeedWithNoContentWhenTheDataDoesNotMatchProvidedIn(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when the data does not match providedIn.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-12';
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[5];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$ltcsProvisionReport->officeId}] is not found");
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
