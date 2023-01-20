<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProvisionReport status のテスト.
 * PUT /ltcs-provision-reports/{officeId}/{userId}/{providedIn}/status
 */
class UpdateLtcsProvisionReportStatusCest extends LtcsProvisionReportTest
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * status が fixed の場合 fixedAt に now がセットされるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithFixedAtSetToNow(ApiTester $I)
    {
        $I->wantTo('succeed API call with fixedAt set to now');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();
        $I->assertSame(Carbon::now()->format(Carbon::ISO8601), $actual['ltcsProvisionReport']['fixedAt']);
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::inProgress()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();
        $I->assertSame(null, $actual['ltcsProvisionReport']['fixedAt']);
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'officeId' => $this->examples->offices[4]->id,
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "LtcsProvisionReport(officeId: {$ltcsProvisionReport->officeId}, userId: {$ltcsProvisionReport->userId}, providedIn: {$providedIn}) not found."
        );
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
        $I->sendPUT(
            "ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
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
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[5];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[1]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$this->examples->offices[1]->id}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[14]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}/status",
            ['status' => LtcsProvisionReportStatus::fixed()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
