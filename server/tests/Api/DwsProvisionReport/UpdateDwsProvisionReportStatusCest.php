<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport status のテスト.
 * PUT /dws-provision-reports/{officeId}/{userId}/{providedIn}/status
 */
class UpdateDwsProvisionReportStatusCest extends DwsProvisionReportTest
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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $dwsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * status が fixed でない場合 fixedAt に null がセットされるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithFixedAtSetToNull(ApiTester $I)
    {
        $I->wantTo('succeed API call with fixedAt set to null');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $dwsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();
        $I->assertSame(null, $actual['dwsProvisionReport']['fixedAt']);
    }

    /**
     * 契約が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenContractNotFound(ApiTester $I)
    {
        $I->wantTo('fail with not found when contract not found');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'officeId' => $this->examples->offices[4]->id,
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'No contracts');
    }

    /**
     * 予実が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenDataDoesNotExistInDb(ApiTester $I)
    {
        $I->wantTo('fail when data does not exist in db');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2099-01';
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "DwsProvisionReport(officeId: {$dwsProvisionReport->officeId}, userId: {$dwsProvisionReport->userId}, providedIn: {$providedIn}) not found."
        );
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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );
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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[5]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$dwsProvisionReport->officeId}] is not found");
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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$this->examples->users[1]->id}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$this->examples->offices[1]->id}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$this->examples->users[14]->id}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

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
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'status' => DwsProvisionReportStatus::inProgress(),
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => DwsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
