<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * user-billings/deposit-registration POST のテスト
 */
class UpdateUserBillingDepositCest extends UserBillingTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        // 入金日登録可能なIDリストを得る
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = [
            'ids' => Seq::fromArray($this->examples->userBillings)
                ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
                ->filter(fn (UserBilling $x): bool => $x->depositedAt === null)
                ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal())
                ->map(fn (UserBilling $x): int => $x->id)
                ->toArray(),
            'depositedOn' => Carbon::now(),
        ];
        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $I->sendPOST('user-billings/deposit-registration', $param);

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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求の入金日が更新されました', [
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
     * 入金日登録時に請求結果が入金済に更新されることを確認するテスト.
     *
     * @param \ApiTester $I
     */
    public function updatedUserBillingResultWhenRegisterDepositDate(ApiTester $I)
    {
        $I->wantTo('updated userBillingResult when register depositDate.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[4]->id;
        $param = [
            'ids' => [$id],
            'depositedOn' => Carbon::now(),
        ];
        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $I->sendPOST('user-billings/deposit-registration', $param);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(4);

        $I->seeResponseContainsJson($expected);

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected['userBilling']['result'], UserBillingResult::paid()->value());
    }

    /**
     * IDの事業者が異なっていると400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('user-billings/deposit-registration', ['ids' => [$this->examples->userBillings[3]->id]]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * IDが認可された事業所にいない場合400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $I->sendPOST('user-billings/deposit-registration', ['ids' => [$this->examples->userBillings[2]->id]]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 入金日に未来日を指定した場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenFutureDateGivenForDepositedOn(ApiTester $I)
    {
        $I->wantTo('failed with bad request when future date given for depositedOn');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $I->sendPOST('user-billings/deposit-registration', ['depositedOn' => Carbon::tomorrow()]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['depositedOn' => [Carbon::tomorrow()->toDateString() . 'より前の日付を入力してください。']]]);
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

        $I->sendPOST('user-billings/deposit-registration', ['ids' => [$this->examples->userBillings[5]->id]]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
