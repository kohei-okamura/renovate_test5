<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OfficeGroup;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup get のテスト.
 * GET /office-groups/{id}
 */
class GetOfficeGroupCest extends OfficeGroupTest
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
        $officeGroup = $this->examples->officeGroups[0];
        $expected = $this->domainToArray(compact('officeGroup'));

        $I->sendGET("/office-groups/{$officeGroup->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseJson($expected);
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

        $I->sendGET("/office-groups/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OfficeGroup({$id}) not found");
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
        $officeGroup = $this->examples->officeGroups[0];

        $I->sendGET("/office-groups/{$officeGroup->id}");
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * IDが許可された事業者に所属していない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenGroupIdIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not permitted Office');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeGroup = $this->examples->officeGroups[3];

        $I->sendGET("/office-groups/{$officeGroup->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OfficeGroup({$officeGroup->id}) not found");
    }
}
