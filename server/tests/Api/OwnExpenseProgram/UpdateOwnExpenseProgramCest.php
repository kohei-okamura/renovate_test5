<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OwnExpenseProgram;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OwnExpenseProgram update のテスト.
 * PUT /own-expense-programs/{id}
 */
class UpdateOwnExpenseProgramCest extends OwnExpenseProgramTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

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
        $ownExpenseProgram = $this->examples->ownExpensePrograms[0]->copy($this->defaultParam());
        $I->sendPUT("/own-expense-programs/{$ownExpenseProgram->id}", $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '自費サービス情報が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ownExpenseProgram->id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET("/own-expense-programs/{$ownExpenseProgram->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfIdDoesNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if id does not exist.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $ownExpenseProgram = $this->examples->ownExpensePrograms[0]->copy($this->defaultParam());
        $I->sendPUT("/own-expense-programs/{$id}", $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "OwnExpenseProgram({$id}) not found"
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
        $ownExpenseProgram = $this->examples->ownExpensePrograms[0]->copy($this->defaultParam());
        $I->sendPUT("/own-expense-programs/{$ownExpenseProgram->id}", $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return [
            'name' => '洗濯',
            'note' => '更新された備考',
        ];
    }
}
