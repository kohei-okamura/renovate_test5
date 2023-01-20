<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OwnExpenseProgram;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OwnExpenseProgram get のテスト.
 * GET /own-expense-programs/{id}
 */
class GetOwnExpenseProgramCest extends OwnExpenseProgramTest
{
    use ExamplesConsumer;

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
        $ownExpenseProgram = $this->examples->ownExpensePrograms[0];
        $expected = $this->domainToArray(compact('ownExpenseProgram'));

        $I->sendGET("own-expense-programs/{$ownExpenseProgram->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
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

        $I->sendGET("own-expense-programs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OwnExpenseProgram({$id}) not found");
    }

    /**
     * IDが事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->ownExpensePrograms[3]->id;

        $I->sendGET("own-expense-programs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OwnExpenseProgram({$id}) not found");
    }

    /**
     * IDがアクセス可能な事業所に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when id is not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->ownExpensePrograms[2]->id;

        $I->sendGET("own-expense-programs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OwnExpenseProgram({$id}) not found");
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
        $id = $this->examples->ownExpensePrograms[0]->id;

        $I->sendGET("own-expense-programs/{$id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
