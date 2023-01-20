<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\BankAccount\BankAccountType;
use Domain\User\PaymentMethod;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserBilling update のテスト.
 * PUT /user-billings/{id}
 */
class UpdateUserBillingCest extends UserBillingTest
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

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[2]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeLogCount(1);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 障害福祉サービス明細 (dwsItem) が存在しなくても更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfDwsItemIsNothing(ApiTester $I)
    {
        $I->wantTo('succeed API call if dwsItem is nothing');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[22]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 介護保険サービス明細 (ltcsItem) が存在しなくても更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfLtcsItemIsNothing(ApiTester $I)
    {
        $I->wantTo('succeed API call if ltcsItem is nothing');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[23]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * その他サービス明細 (otherItems) が空でも更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfOtherItemsIsEmpty(ApiTester $I)
    {
        $I->wantTo('succeed API call if otherItems is empty');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[24]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 支払方法を銀行振込に更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfPaymentMethodChangesToTransfer(ApiTester $I)
    {
        $I->wantTo('succeed api call if payment method changes to transfer');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[22]->id;
        $param = $this->defaultParam(['paymentMethod' => PaymentMethod::transfer()->value()]);
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 支払方法を集金に更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfPaymentMethodChangesToCollection(ApiTester $I)
    {
        $I->wantTo('succeed api call if payment method changes to collection');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[22]->id;
        $param = $this->defaultParam(['paymentMethod' => PaymentMethod::collection()->value()]);
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 口座情報を更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallIfUserBillingBankAccountChanges(ApiTester $I)
    {
        $I->wantTo('succeed api call if user billing bank account changes');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[2]->id;
        $param = $this->defaultParam([
            'paymentMethod' => PaymentMethod::withdrawal()->value(),
            'bankAccount' => [
                'bankName' => '銀行名',
                'bankCode' => '1234',
                'bankBranchName' => '支店名',
                'bankBranchCode' => '567',
                'bankAccountType' => BankAccountType::currentDeposit()->value(),
                'bankAccountNumber' => '1234567',
                'bankAccountHolder' => 'メイ ギ',
            ],
        ]);
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者請求が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGet("/user-billings/{$id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 請求結果が未処理でない場合に更新できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenResultIsNotPending(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when result is not pending');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[8]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['利用者請求を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 処理日時が null でない場合に更新できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenTransactedAtIsNotNull(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when transactedAt is not null');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[9]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['利用者請求を更新できません。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 支払方法を未設定に更新できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenPaymentMethodChangesToNone(ApiTester $I)
    {
        $I->wantTo('fail with bad request when payment method changes to none');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[0]->id;
        $param = $this->defaultParam(['paymentMethod' => PaymentMethod::none()->value()]);
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['paymentMethod' => ['変更できない支払方法が指定されました。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 支払方法を口座振替に更新できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenPaymentMethodChangesToWithdrawal(ApiTester $I)
    {
        $I->wantTo('fail with bad request when payment method changes to withdrawal');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->userBillings[0]->id;
        $param = $this->defaultParam(['paymentMethod' => PaymentMethod::withdrawal()->value()]);
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['paymentMethod' => ['変更できない支払方法が指定されました。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfIdDoesNotExist(ApiTester $I)
    {
        $I->wantTo('fail with Not Found if id does not exist.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "UserBilling({$id}) not found"
        );
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
        $id = $this->examples->userBillings[2]->id;
        $param = $this->defaultParam();
        $I->sendPut("/user-billings/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param array $values
     * @return array
     */
    private function defaultParam(array $values = []): array
    {
        return array_merge(
            [
                'carriedOverAmount' => -1000,
                'paymentMethod' => PaymentMethod::transfer()->value(),
                'bankAccount' => [
                    'bankName' => '',
                    'bankCode' => '',
                    'bankBranchName' => '',
                    'bankBranchCode' => '',
                    'bankAccountType' => BankAccountType::unknown()->value(),
                    'bankAccountNumber' => '',
                    'bankAccountHolder' => '',
                ],
            ],
            $values,
        );
    }
}
