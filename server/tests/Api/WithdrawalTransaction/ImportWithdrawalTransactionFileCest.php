<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\WithdrawalTransaction;

use ApiTester;
use Codeception\Util\HttpCode;
use DateTime;
use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Lib\Json;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Shift import のテスト.
 * POST /shift-imports
 */
class ImportWithdrawalTransactionFileCest extends WithdrawalTransactionTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_valid.txt');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::success()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));

        // 該当する利用者請求の入金日時、請求結果、振替結果コード、処理日時が更新されていることを検証
        $ids = [
            $this->examples->userBillings[16]->id,
            $this->examples->userBillings[17]->id,
            $this->examples->userBillings[18]->id,
            $this->examples->userBillings[19]->id,
            $this->examples->userBillings[20]->id,
            $this->examples->userBillings[21]->id,
        ];
        foreach ($ids as $id) {
            $I->sendGET("/user-billings/{$id}");
            $actual = $I->grabResponseArray()['userBilling'];

            $I->assertSame(Carbon::parse('2022-02-28')->format(DateTime::ISO8601), $actual['depositedAt']);
            $I->assertSame(UserBillingResult::paid()->value(), $actual['result']);
            $I->assertSame(WithdrawalResultCode::done()->value(), $actual['withdrawalResultCode']);
            $I->assertSame(Carbon::now()->format(DateTime::ISO8601), $actual['transactedAt']);
        }
    }

    /**
     * 振替に失敗した利用者請求を含む場合、その請求結果が「口座振替未済」になるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithResultUnpaiedWhenWithdrawalTransactionIsFailed(ApiTester $I)
    {
        $I->wantTo('succeed API call with result unpaied when withdrawal transaction is failed');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_valid_with_shortage.txt');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::success()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));

        // 該当する利用者請求の入金日時、請求結果、振替結果コード、処理日時が更新されていることを検証
        $succeededIds = [
            $this->examples->userBillings[16]->id,
            $this->examples->userBillings[17]->id,
            $this->examples->userBillings[18]->id,
        ];
        foreach ($succeededIds as $id) {
            $I->sendGET("/user-billings/{$id}");
            $actual = $I->grabResponseArray()['userBilling'];

            $I->assertSame(Carbon::parse('2022-02-28')->format(DateTime::ISO8601), $actual['depositedAt']);
            $I->assertSame(UserBillingResult::paid()->value(), $actual['result']);
            $I->assertSame(WithdrawalResultCode::done()->value(), $actual['withdrawalResultCode']);
            $I->assertSame(Carbon::now()->format(DateTime::ISO8601), $actual['transactedAt']);
        }
        $failedIds = [
            $this->examples->userBillings[19]->id,
            $this->examples->userBillings[20]->id,
            $this->examples->userBillings[21]->id,
        ];
        foreach ($failedIds as $id) {
            $I->sendGET("/user-billings/{$id}");
            $actual = $I->grabResponseArray()['userBilling'];

            $I->assertSame(null, $actual['depositedAt']);
            $I->assertSame(UserBillingResult::unpaid()->value(), $actual['result']);
            $I->assertSame(WithdrawalResultCode::shortage()->value(), $actual['withdrawalResultCode']);
            $I->assertSame(Carbon::now()->format(DateTime::ISO8601), $actual['transactedAt']);
        }
    }

    /**
     * 全銀ファイルでないファイルを指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithStatusFailureWhenSpecifyNotZenginFile(ApiTester $I)
    {
        $I->wantTo('fail with status failure when specify not zengin file');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('DummyFileForDownloadTest.txt');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(['error' => ['全銀ファイルではありません。']], $job['data']);
    }

    /**
     * 該当する利用者請求が存在しない場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param ApiTester $I
     */
    public function failWithStatusFailureWhenNotFoundUserBilling(ApiTester $I)
    {
        $I->wantTo('fail with status failure when not found user billing');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_not_found_user_billing.txt');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(['error' => ['該当するデータがありません。']], $job['data']);
    }

    /**
     * 引落日が6ヶ月以上前の全銀ファイルを指定した場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param ApiTester $I
     */
    public function failWithStatusFailureWhenSpecify6MonthsAgoZenginFile(ApiTester $I)
    {
        $I->wantTo('fail with status failure when specify 6 months ago zengin file');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_seven_months_ago.txt');

        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(['error' => ['6ヶ月以上前の全銀ファイルはアップロードできません。']], $job['data']);
    }

    /**
     * 振替結果データが不正である場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param ApiTester $I
     */
    public function failWithStatusFailureWhenWithdrawalResultIsInvalid(ApiTester $I)
    {
        $I->wantTo('fail with status failure when withdrawal result is invalid');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_invalid_withdrawal_result.txt');

        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(['error' => ['振替結果データが不正のため処理できません。']], $job['data']);
    }

    /**
     * アップロード済みファイルの場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param ApiTester $I
     */
    public function failWithStatusFailureWhenFileIsAlreadyUploaded(ApiTester $I)
    {
        $I->wantTo('fail with status failure when the file is already uploaded');

        Carbon::setTestNow('2022-05-15 00:00:00');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $filepath = codecept_data_dir('UserBilling/zengin_valid.txt');
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');

        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'status' => JobStatus::success()->value(),
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));

        // 2回目のアップロード
        $I->sendPOST('/withdrawal-transaction-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
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

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(['error' => ['アップロード済みのファイルです。']], $job['data']);
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
        $filepath = codecept_data_dir('UserBilling/zengin_valid.txt');

        $I->sendPOST('/shift-imports', [], $this->buildFile($filepath));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
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
                'type' => 'text/plain',
                'size' => filesize($filepath),
                'tmp_name' => $filepath,
                'error' => \UPLOAD_ERR_OK,
            ],
        ];
    }
}
