<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OrganizationSetting;

use ApiTester;
use Codeception\Util\HttpCode;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OrganizationSetting get のテスト.
 * GET /setting/{id}
 */
class GetOrganizationSettingCest extends OrganizationSettingTest
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
        $organizationSetting = $this->examples->organizationSettings[0];
        $expected = $this->domainToArray(compact('organizationSetting'));

        $I->sendGET('setting');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
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

        $I->sendGET('setting');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
