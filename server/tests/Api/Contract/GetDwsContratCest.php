<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Contract;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Contract Get のテスト.
 * GET /users/{userId}/dws-contracts/{id}
 */
class GetDwsContratCest extends ContractTest
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
        $contract = $this->examples->contracts[0];
        $expected = $this->domainToArray($contract);

        $I->sendGET("users/{$contract->userId}/dws-contracts/{$contract->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * ユーザIDが文字列のテスト
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWheneUserIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when userId is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $contract = $this->examples->contracts[0];

        $I->sendGET("users/userId/dws-contracts/{$contract->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * ユーザIDが無効の時のテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidUserId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid UserId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $contract = $this->examples->contracts[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$userId}/dws-contracts/{$contract->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 契約IDが文字列のテスト
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when id is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $contract = $this->examples->contracts[0];

        $I->sendGET("users/{$contract->userId}/dws-contracts/id");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 契約IDが無効の時のテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $contract = $this->examples->contracts[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$contract->userId}/dws-contracts/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Contract({$id}) not found");
    }

    /**
     * 事業者が異なる利用者で404となるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     */
    public function failedWithNotFoundWhenUserIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when User is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);
        $user = $this->examples->users[14];
        $userId = $user->id;
        $id = $this->examples->contracts[0]->id;

        $I->sendGET("users/{$userId}/dws-contracts/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 事業者が異なる契約IDで404となるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     */
    public function failedWithNotFoundWhenIdIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);
        $user = $this->examples->users[0];
        $userId = $user->id;
        $id = $this->examples->contracts[3]->id;

        $I->sendGET("users/{$userId}/dws-contracts/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Contract({$id}) not found");
    }

    /**
     * アクセス可能なOfficeと契約がない利用者を指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $userId = $this->examples->users[1]->id;
        $id = $this->examples->contracts[0]->id;

        $I->sendGET("users/{$userId}/dws-contracts/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
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
        $contract = $this->examples->contracts[0];

        $I->sendGET("users/{$contract->userId}/dws-contracts/{$contract->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
