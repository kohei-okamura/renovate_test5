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
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserBilling deleteDepositのテスト.
 * PUT /user-billings/deposit-cancellation
 */
class DeleteUserBillingDepositCest extends UserBillingTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API Call.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal())
            ->filter(fn (UserBilling $x): bool => $x->depositedAt !== null)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('/user-billings/deposit-cancellation', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        // ログ確認
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
        $I->seeLogMessage(1, LogLevel::INFO, '利用者請求入金日が削除されました', [
            'id' => '',  // TODO DEV-1577
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);
    }

    /**
     * 入金日削除時に請求結果が未処理に更新されることを確認するテスト.
     *
     * @param ApiTester $I
     */
    public function updatedUserBillingResultWhenDeleteDepositDate(ApiTester $I)
    {
        $I->wantTo('updated userBillingResult when delete depositDate.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[5]->id;
        $ids = [$id];

        $I->sendPost('/user-billings/deposit-cancellation', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        // ログ確認
        $I->seeLogCount(4);

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected['userBilling']['result'], UserBillingResult::pending()->value());
    }

    /**
     * IDが事業者に所属していない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when ID is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId !== $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal())
            ->filter(fn (UserBilling $x): bool => $x->depositedAt !== null)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('/user-billings/deposit-cancellation', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * IDがアクセス可能な事業所に所属していない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when ID is not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => !in_array($x->officeId, $staff->officeIds, true))
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal())
            ->filter(fn (UserBilling $x): bool => $x->depositedAt !== null)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('/user-billings/deposit-cancellation', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['正しい値を入力してください。']]]);
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
        $ids = Seq::fromArray($this->examples->userBillings)
            ->filter(fn (UserBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal())
            ->filter(fn (UserBilling $x): bool => $x->depositedAt !== null)
            ->map(fn (UserBilling $x): int => $x->id)
            ->toArray();

        $I->sendPost('/user-billings/deposit-cancellation', compact('ids'));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
