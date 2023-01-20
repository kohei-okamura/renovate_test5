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
 * LtcsProject Download のテスト.
 * GET /users/{userId}/ltcs-projects/{id}/download
 */
class DownloadLtcsProjectCest extends LtcsProjectTest
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
        $ltcsProject = $this->examples->ltcsProjects[0];

        $I->sendGET("users/{$ltcsProject->userId}/ltcs-projects/{$ltcsProject->id}/download");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->haveHttpHeader('Content-Type', 'application/pdf');
        $I->seeLogCount(0);
    }

    /**
     * IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when id is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->examples->ltcsProjects[0];

        $I->sendGET("users/{$ltcsProject->userId}/ltcs-projects/id/download");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * IDが存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->examples->ltcsProjects[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$ltcsProject->userId}/ltcs-projects/{$id}/download");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsProject[{$id}] not found");
    }

    /**
     * 利用者IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWheneUserIdIsString(ApiTester $I)
    {
        $I->wantTo('failed with not found when userId is string');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->examples->ltcsProjects[0];

        $I->sendGET("users/userId/ltcs-projects/{$ltcsProject->id}/download");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidUserId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid UserId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProject = $this->examples->ltcsProjects[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$userId}/ltcs-projects/{$ltcsProject->id}/download");

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
        $ltcsProject = $this->examples->ltcsProjects[0];

        $I->sendGET("users/{$ltcsProject->userId}/ltcs-projects/{$ltcsProject->id}/download");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
