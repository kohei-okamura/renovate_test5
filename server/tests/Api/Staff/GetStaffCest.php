<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Lib\Json;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staff getのテスト.
 * GET /staff/{id}
 */
class GetStaffCest extends StaffTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Json::decode(Json::encode($staff), true);

        $I->sendGET("staffs/{$staff->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expected);
    }

    /**
     * 他の組織の所属するIDの情報は参照できないテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsOtherOrganizations(ApiTester $I)
    {
        $I->wantTo('failed with NOT FOUND when id is other organization`s');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->staffs[1]->id;

        $I->sendGET("staffs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
    }

    /**
     * IDが許可された事業所に存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $id = $this->examples->staffs[4]->id;

        $I->sendGET("staffs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
    }

    /**
     * Scopeがpersonのときに、自分のID以外にアクセスすると404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotMyselfByScopeIsPerson(ApiTester $I)
    {
        $I->wantTo('failed With NotFound when ID not myself by Scope is person');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $id = $this->examples->staffs[0]->id;

        $I->sendGET("staffs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Staff({$id}) not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[31];
        $I->actingAs($staff);

        $I->sendGET("staffs/{$staff->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
