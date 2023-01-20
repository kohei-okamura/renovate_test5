<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Project;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProject Get のテスト.
 * GET /users/{userId}/dws-projects/{id}
 */
class GetDwsProjectCest extends DwsProjectTest
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
        $dwsProject = $this->examples->dwsProjects[0];
        $expected = $this->domainToArray(compact('dwsProject'));

        $I->sendGET("users/{$dwsProject->userId}/dws-projects/{$dwsProject->id}");

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
        $dwsProject = $this->examples->dwsProjects[0];

        $I->sendGET("users/userId/dws-projects/{$dwsProject->id}");

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
        $dwsProject = $this->examples->dwsProjects[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$userId}/dws-projects/{$dwsProject->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 障害福祉サービス計画IDが文字列のテスト
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when id is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->examples->dwsProjects[0];

        $I->sendGET("users/{$dwsProject->userId}/dws-projects/id");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 障害福祉サービス計画IDが無効の時のテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->examples->dwsProjects[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$dwsProject->userId}/dws-projects/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "DwsProject({$id}) not found");
    }

    /**
     * 利用者IDが同じ事業者に存在しない場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProject = $this->examples->dwsProjects[0];
        $userId = $this->examples->users[14]->id;

        $I->sendGET("users/{$userId}/dws-projects/{$dwsProject->id}");

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
        $dwsProject = $this->examples->dwsProjects[0];

        $I->sendGET("users/{$dwsProject->userId}/dws-projects/{$dwsProject->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
