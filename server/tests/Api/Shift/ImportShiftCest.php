<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Unit\Examples\ExamplesConsumer;
use const UPLOAD_ERR_OK;

/**
 * Shift import のテスト.
 * POST /shift-imports
 */
class ImportShiftCest extends ShiftTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function suceedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('Shift/valid-shifts.xlsx');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeLogCount(4);
        // 本番の順序で検証（まずはWeb側のログが出力される）
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 続いてJOB側のログ
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::inProgress()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '勤務シフトが一括登録されました', [
            'ids' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::success()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertEquals(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));
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
        $filepath = codecept_data_dir('Shift/valid-shifts.xlsx');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 他の事業者の事業所を指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithStatusFailureWhenSpecifyOfficeFromOtherOrganization(ApiTester $I)
    {
        $I->wantTo('fail with status failure when specify office from other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filepath = codecept_data_dir('Shift/another-organization-office-shifts.xlsx');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeLogCount(3);
        // 本番の順序で検証（まずはWeb側のログが出力される）
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 続いてJOB側のログ
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::inProgress()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::failure()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        assertSame(
            [
                'error' => [
                    '「事業所名」は正しい値を入力してください。',
                    '「利用者」は事業所に所属している利用者を指定してください。（行番号9）',
                ],
            ],
            $job['data'],
            Json::encode($job['data'])
        );
    }

    /**
     * 他の事業者に属している利用者を指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithStatusFailureWhenSpecifyUserBelongingToOtherOrganization(ApiTester $I)
    {
        $I->wantTo('fail with status failure when specify user belonging to other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filepath = codecept_data_dir('Shift/another-organization-user-shifts.xlsx');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeLogCount(3);
        // 本番の順序で検証（まずはWeb側のログが出力される）
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 続いてJOB側のログ
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::inProgress()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::failure()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        assertSame(
            [
                'error' => [
                    '「利用者」は正しい値を入力してください。（行番号9）',
                ],
            ],
            $job['data'],
            Json::encode($job['data'])
        );
    }

    /**
     * アクセスできない事業所を指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithStatusFailureWhenSpecifyNotAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with status failure when specify not accessible office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $filepath = codecept_data_dir('Shift/not-accessible-office-shifts.xlsx');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeLogCount(3);
        // 本番の順序で検証（まずはWeb側のログが出力される）
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 続いてJOB側のログ
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::inProgress()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::failure()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        assertSame(
            [
                'error' => [
                    '「事業所名」は正しい値を入力してください。',
                    '「利用者」は事業所に所属している利用者を指定してください。（行番号9）',
                ],
            ],
            $job['data'],
            Json::encode($job['data'])
        );
    }

    /**
     * アクセスできない事業所に属している利用者を指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithStatusFailureWhenAccessingUserBelongingToNotAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with status failure when accessing User belonging to not accessible office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $filepath = codecept_data_dir('Shift/user-belonging-not-accessible-office-shifts.xlsx');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeLogCount(3);
        // 本番の順序で検証（まずはWeb側のログが出力される）
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        // 続いてJOB側のログ
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::inProgress()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::failure()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        assertSame(
            [
                'error' => [
                    '「利用者」は正しい値を入力してください。（行番号9）',
                ],
            ],
            $job['data'],
            Json::encode($job['data'])
        );
    }

    /**
     * ファイルパラメータ組み立て.
     *
     * @param string $filepath
     * @return array|UploadedFile[]
     */
    private function buildFile(string $filepath): array
    {
        return [
            'file' => [
                'name' => basename($filepath),
                'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size' => filesize($filepath),
                'tmp_name' => $filepath,
                'error' => UPLOAD_ERR_OK,
            ],
        ];
    }
}
