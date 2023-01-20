<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\StaffBankAccount;

use ApiTester;
use Codeception\Util\HttpCode;
use Lib\Json;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * StaffBankAccount Update のテスト.
 * PUT /staffs/{staffId}/bank-account
 */
class UpdateStaffBankAccountCest extends StaffBankAccountTest
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
        $param = Json::decode(Json::encode($bankAccount), true);

        $I->sendPUT("staffs/{$this->examples->staffs[2]->id}/bank-account", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'スタッフの銀行口座が更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("staffs/{$this->examples->staffs[2]->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected['bankAccount'], $actual['bankAccount']);
    }

    /**
     * StaffIdが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenStaffIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when StaffId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $bankAccount = $this->examples->bankAccounts[0];
        $param = Json::decode(Json::encode($bankAccount), true);
        $staffId = self::NOT_EXISTING_ID;

        $I->sendPUT("staffs/{$staffId}/bank-account", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$staffId}) not found");
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
        $bankAccount = $this->examples->bankAccounts[0];
        $param = Json::decode(Json::encode($bankAccount), true);

        $I->sendPUT("staffs/{$staff->id}/bank-account", $param);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    // NOTE: 認可対応後に追加するケース
    // - staffがorganization外だと操作できない
    // - staffが許可されたoffice外だと操作できない
}
