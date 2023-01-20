<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserBankAccount;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * PUT /users/{userId}/bank-account のテスト
 */
class UpdateUserBankAccountCest extends UserBankAccountTest
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
        $bankAccount = $this->examples->bankAccounts[0];
        $param = $this->domainToArray($bankAccount);

        $I->sendPUT("users/{$this->examples->users[0]->id}/bank-account", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '利用者の銀行口座が更新されました', [
            'id' => $bankAccount->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$this->examples->users[0]->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected['bankAccount'], $actual['bankAccount']);
    }

    /**
     * UserIdが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $bankAccount = $this->examples->bankAccounts[0];
        $param = $this->domainToArray($bankAccount);
        $userId = self::NOT_EXISTING_ID;

        $I->sendPUT("users/{$userId}/bank-account", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * アクセス可能なOfficeと契約がない利用者の情報を更新すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIsNotInOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserId not exists');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $bankAccount = $this->examples->bankAccounts[0];
        $param = $this->domainToArray($bankAccount);
        $userId = $this->examples->users[1]->id;

        $I->sendPUT("users/{$userId}/bank-account", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    // NOTE: 認可対応後に追加するケース
    // - userがorganization外だと操作できない
}
