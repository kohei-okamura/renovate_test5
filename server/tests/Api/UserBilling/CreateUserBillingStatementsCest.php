<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Domain\Staff\Staff;
use Domain\UserBilling\UserBilling;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserBilling createStatement のテスト.
 * POST /user-billing-statements
 */
class CreateUserBillingStatementsCest extends UserBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出し テスト
     *
     * @param ApiTester $I
     */
    public function suceedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = $this->createIds($staff);
        $issuedOn = '2021-11-10T00:00:00Z';

        $I->sendPost('/user-billing-statements', compact('ids', 'issuedOn'));

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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求：介護サービス利用明細書生成ジョブ終了', [
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
     * IDが指定されていない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenIdIsNoting(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when ID is nothing');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPost('/user-billing-statements', []);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * サービス内容が自費サービスのみ（障害福祉サービス、介護保険サービスの両方を含まない）の利用者請求のIDが指定されている場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenContainOwnExpenseServiceOnlyUserBillingId(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when contain own expense service only user billing id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = $this->createIds($staff, true);

        $I->sendPost('/user-billing-statements', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['障害福祉サービス、もしくは介護保険サービスを含む利用者請求を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 発行日が指定されていない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenIssuedOnIsNoting(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when issuedOn is nothing');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $ids = $this->createIds($staff);

        $I->sendPost('/user-billing-statements', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['issuedOn' => ['入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 発行日が不正な日付の場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenIssuedOnIsInvalidDate(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when issuedOn is invalid date');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $ids = $this->createIds($staff);
        $issuedOn = '2021-13-01T00:00:00Z';

        $I->sendPost('/user-billing-statements', compact('ids', 'issuedOn'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['issuedOn' => ['正しい日付を入力してください。']]]);
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
        $ids = $this->createIds($staff);

        $I->sendPost('/user-billing-statements', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    private function createIds(Staff $staff, bool $hasOwnExpenseOnly = false): array
    {
        return Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(function (UserBilling $x) use ($hasOwnExpenseOnly) {
                return $hasOwnExpenseOnly
                     ? $x->dwsItem === null && $x->ltcsItem === null
                     : $x->dwsItem !== null || $x->ltcsItem !== null;
            })
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();
    }
}
