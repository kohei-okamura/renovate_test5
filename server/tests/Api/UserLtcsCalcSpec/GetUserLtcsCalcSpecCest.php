<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserLtcsCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserLtcsCalcSpec get のテスト.
 * GET /users/{userId}/ltcs-calc-specs/{id}
 */
class GetUserLtcsCalcSpecCest extends UserLtcsCalcSpecTest
{
    use ExamplesConsumer;

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
        $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];
        $expected = $this->domainToArray(compact('ltcsCalcSpec'));

        $I->sendGET("/users/{$ltcsCalcSpec->userId}/ltcs-calc-specs/{$ltcsCalcSpec->id}");

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
        $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("/users/{$ltcsCalcSpec->userId}/ltcs-calc-specs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "UserLtcsCalcSpec({$id}) not found");
    }

    /**
     * 別の事業者の利用者 ID が指定された場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenUserIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when user id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[3];

        $I->sendGET("/users/{$ltcsCalcSpec->userId}/ltcs-calc-specs/{$ltcsCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$ltcsCalcSpec->userId}] is not found");
    }

    /**
     * アクセス可能でない利用者 ID が指定された場合に404が返るテスト（認可）.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when user id is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[2];

        $I->sendGET("/users/{$ltcsCalcSpec->userId}/ltcs-calc-specs/{$ltcsCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$ltcsCalcSpec->userId}] is not found");
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
        $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];

        $I->sendGET("/users/{$ltcsCalcSpec->userId}/ltcs-calc-specs/{$ltcsCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
