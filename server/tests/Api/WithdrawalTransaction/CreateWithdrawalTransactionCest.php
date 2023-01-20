<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\WithdrawalTransaction;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\WithdrawalTransactionFinder;
use Lib\Json;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * WithdrawalTransaction create のテスト
 * POST /withdrawal-transactions
 */
class CreateWithdrawalTransactionCest extends WithdrawalTransactionTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        Carbon::setTestNow('2021-09-01 00:00:00');
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $userBillingIds = [
            $this->examples->userBillings[10]->id,
            $this->examples->userBillings[11]->id,
            $this->examples->userBillings[12]->id,
        ];

        $I->sendPost('withdrawal-transactions', compact('userBillingIds'));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

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
        $I->seeLogMessage(1, LogLevel::INFO, '口座振替データが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        /** @var \Domain\UserBilling\WithdrawalTransactionFinder $withdrawalTransactionFinder */
        $withdrawalTransactionFinder = app(WithdrawalTransactionFinder::class);
        $withdrawalTransaction = $withdrawalTransactionFinder
            ->find([], ['all' => true, 'sortBy' => 'id'])
            ->list
            ->last();
        $I->assertMatchesModelSnapshot($withdrawalTransaction);
    }

    /**
     * 請求結果が未処理以外の利用者請求IDが指定された場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenUserBillingIDWhoseResultIsPendingContain(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when userBillingIds whose result is pending contain');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $userBillingIds = [
            $this->examples->userBillings[0]->id,
            $this->examples->userBillings[11]->id,
            $this->examples->userBillings[12]->id,
        ];

        $I->sendPost('withdrawal-transactions', compact('userBillingIds'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userBillingIds' => ['全銀ファイル作成済みの利用者請求IDが含まれています。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 請求金額が0より大きいものが存在しない場合にジョブ状態がfailure（失敗）になるテスト.
     *
     * @param ApiTester $I
     */
    public function failWithStatusFailureWhenAmountGreaterThanZeroDoesNotExist(ApiTester $I)
    {
        $I->wantTo('fail with status failure when amount greater than zero does not exist');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $userBillingIds = [
            $this->examples->userBillings[13]->id,
        ];

        $I->sendPost('withdrawal-transactions', compact('userBillingIds'));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::failure()->value(),
        ]);

        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        $I->assertSame(JobStatus::failure()->value(), $job['status'], Json::encode($job['data'] ?? []));
        $I->assertSame(
            ['error' => ['全ての請求金額が0円となるため全銀ファイルを作成できません。']],
            $job['data'],
            Json::encode($job['data'])
        );
    }

    /**
     * 別事業者の利用者請求IDが指定された場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenUserBillingIDFromOtherOrganizationIsSpecified(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when UserBilling ID from other organization is specified');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $userBillingIds = Seq::from(...$this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId !== $staff->organizationId)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('withdrawal-transactions', compact('userBillingIds'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userBillingIds' => ['正しい値を入力してください。']]]);
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

        $userBillingIds = Seq::from(...$this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod === PaymentMethod::withdrawal())
            ->filter(fn (UserBilling $x): bool => $x->transactedAt === null)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('withdrawal-transactions', compact('userBillingIds'));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
