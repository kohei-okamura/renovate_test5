<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsProvisionReport;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProvisionReport createSheet のテスト.
 * POST /ltcs-provision-report-sheets
 */
class CreateLtcsProvisionReportSheetCest extends Test
{
    use ExamplesConsumer;

    /**
     * API正常呼び出し テスト
     *
     * @param \BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $provisionReport = $this->examples->ltcsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');
        $issuedOn = '2021-11-10T00:00:00Z';
        $needsMaskingInsName = true;
        $needsMaskingInsNumber = true;

        $I->sendPost('/ltcs-provision-report-sheets', compact('officeId', 'userId', 'providedIn', 'issuedOn', 'needsMaskingInsName', 'needsMaskingInsNumber'));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(4);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '介護保険サービス：サービス提供票生成ジョブ終了', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertEquals(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));
    }

    /**
     * アクセスできない事業所ID を指定した場合に400が返るテスト（認可）.
     *
     * @param \BillingTester $I
     */
    public function failWithBadRequestWhenOfficeIdIsNotAccessible(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when officeId is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $provisionReport = $this->examples->ltcsProvisionReports[0];
        $officeId = $this->examples->offices[1]->id;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');
        $issuedOn = '2021-11-10T00:00:00Z';
        $needsMaskingInsName = true;
        $needsMaskingInsNumber = true;

        $I->sendPost('/ltcs-provision-report-sheets', compact('officeId', 'userId', 'providedIn', 'issuedOn', 'needsMaskingInsName', 'needsMaskingInsNumber'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * アクセスできない利用者ID を指定した場合に400が返るテスト（認可）.
     *
     * @param \BillingTester $I
     */
    public function failWithBadRequestWhenUserIdIsNotAccessible(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when user id is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $provisionReport = $this->examples->ltcsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $this->examples->users[1]->id;
        $providedIn = $provisionReport->providedIn->format('Y-m');
        $issuedOn = '2021-11-10T00:00:00Z';
        $needsMaskingInsName = true;
        $needsMaskingInsNumber = true;

        $I->sendPost('/ltcs-provision-report-sheets', compact('officeId', 'userId', 'providedIn', 'issuedOn', 'needsMaskingInsName', 'needsMaskingInsNumber'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(BillingTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $provisionReport = $this->examples->ltcsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');
        $issuedOn = '2021-11-10T00:00:00Z';
        $needsMaskingInsName = true;
        $needsMaskingInsNumber = true;

        $I->sendPost('/ltcs-provision-report-sheets', compact('officeId', 'userId', 'providedIn', 'issuedOn', 'needsMaskingInsName', 'needsMaskingInsNumber'));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
