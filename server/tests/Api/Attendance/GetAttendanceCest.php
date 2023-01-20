<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Attendance get のテスト.
 * GET /attendances/{id}
 */
class GetAttendanceCest extends AttendanceTest
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
        $attendance = $this->examples->attendances[0];
        $expected = $this->domainToArray($attendance);

        $I->sendGET("attendances/{$attendance->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotExist(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not exist.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("attendances/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
    }

    /**
     * IDが事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->attendances[2]->id;

        $I->sendGET("attendances/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
    }

    /**
     * IDがアクセス可能な事業所に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when id is not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->attendances[3]->id;

        $I->sendGET("attendances/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
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
        $id = $this->examples->attendances[0]->id;

        $I->sendGET("attendances/{$id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
