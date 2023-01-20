<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * user-billings/user-billing-notices POST のテスト
 */
class CreateUserBillingNoticeCest extends UserBillingTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        // 代理受領額通知書生成可能なIDリストを得る
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $params = [
            'ids' => [1],
            'issuedOn' => '2021-11-10T00:00:00Z',
        ];

        $I->sendPost('/user-billing-notices', $params);

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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求：代理受領額通知書生成ジョブ終了', [
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
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendPOST('/user-billing-notices', ['ids' => [$this->examples->userBillings[5]->id]]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
