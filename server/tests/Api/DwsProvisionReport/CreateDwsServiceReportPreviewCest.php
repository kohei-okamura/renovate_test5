<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport createPreview のテスト.
 * POST /dws-service-report-previews
 */
class CreateDwsServiceReportPreviewCest extends Test
{
    use ExamplesConsumer;

    /**
     * API正常呼び出し テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $provisionReport = $this->examples->dwsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');

        $I->sendPost('/dws-service-report-previews', compact('officeId', 'userId', 'providedIn'));

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
        $I->seeLogMessage(1, LogLevel::INFO, 'サービス提供実績記録票（プレビュー版）生成ジョブ終了', [
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
     * アクセスできない事業所 ID を指定した場合に400が返るテスト（認可）.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOfficeIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when officeId is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $provisionReport = $this->examples->dwsProvisionReports[0];
        $officeId = $this->examples->offices[1]->id;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');

        $I->sendPost('/dws-service-report-previews', compact('officeId', 'userId', 'providedIn'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * アクセスできない利用者 ID を指定した場合に400が返るテスト（認可）.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenUserIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when user id is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $provisionReport = $this->examples->dwsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $this->examples->users[1]->id;
        $providedIn = $provisionReport->providedIn->format('Y-m');

        $I->sendPost('/dws-service-report-previews', compact('officeId', 'userId', 'providedIn'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
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

        $provisionReport = $this->examples->dwsProvisionReports[0];
        $officeId = $provisionReport->officeId;
        $userId = $provisionReport->userId;
        $providedIn = $provisionReport->providedIn->format('Y-m');

        $I->sendPost('/dws-service-report-previews', compact('officeId', 'userId', 'providedIn'));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
