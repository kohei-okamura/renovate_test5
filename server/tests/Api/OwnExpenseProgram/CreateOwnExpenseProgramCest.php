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
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OwnExpenseProgram create のテスト.
 * POST /own-expense-programs
 */
class CreateOwnExpenseProgramCest extends OwnExpenseProgramTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * API正常呼び出し テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('own-expense-programs', $this->domainToArray($this->examples->ownExpensePrograms[0]));
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '自費サービス情報が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 異なる事業者のofficeIdの場合400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOfficeIdIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('fail with BAD REQUEST when officeId is outside organization.');

        $staff = $this->examples->staffs[30]; // 管理者
        $I->actingAs($staff);
        $officeId = $this->examples->offices[1]->id; // 異なる事業者の事業所ID
        $param = ['officeId' => $officeId] + $this->domainToArray($this->examples->ownExpensePrograms[0]);

        $I->sendPOST('own-expense-programs', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能でないOfficeIdの場合400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOfficeIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when OfficeId is not accessible');

        $staff = $this->examples->staffs[28]; // 事業所管理者
        $I->actingAs($staff);
        $officeId = $this->examples->offices[2]->id; // 事業者は同じだが別の事業所のID
        $param = ['officeId' => $officeId] + $this->domainToArray($this->examples->ownExpensePrograms[0]);

        $I->sendPOST('own-expense-programs', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendPOST('own-expense-programs', $this->domainToArray($this->examples->ownExpensePrograms[0]));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
