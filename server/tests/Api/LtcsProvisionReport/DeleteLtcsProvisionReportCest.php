<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProvisionReport delete のテスト.
 * DELETE /ltcs-provision-reports/{officeId}/{userId}/{providedIn}
 */
class DeleteLtcsProvisionReportCest extends LtcsProvisionReportTest
{
    use ExamplesConsumer;
    use TransactionMixin;

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}"
        );

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が削除されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $I->sendGet("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * 予実が確定済みの場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenEntityIsFixed(ApiTester $I)
    {
        $I->wantTo('fail when entity is fixed.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[3];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['plans' => ['確定済みの予実は編集できません。']]]);
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}");

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

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$ltcsProvisionReport->officeId}] is not found");
    }

    /**
     * アクセス可能な利用者でない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleUser(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible user.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[1]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[1]->id}] is not found");
    }

    /**
     * 事業所が事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenOfficeIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$this->examples->offices[1]->id}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$this->examples->offices[1]->id}] is not found");
    }

    /**
     * 利用者が事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenUserIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when User is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[14]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[14]->id}] is not found");
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[6];

        $I->sendDelete("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
