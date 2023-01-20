<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\HomeVisitLongTermCareCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * HomeVisitLongTermCareCalcSpec identify のテスト.
 * GET /offices/{officeId}/home-visit-long-term-care-calc-specs
 */
class IdentifyHomeVisitLongTermCareCalcSpecCest extends HomeVisitLongTermCareCalcSpecTest
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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[4];
        $providedIn = '2021-06';
        $expected = $this->domainToArray(compact('homeVisitLongTermCareCalcSpec'));

        $I->sendGet("offices/{$homeVisitLongTermCareCalcSpec->officeId}/home-visit-long-term-care-calc-specs?providedIn={$providedIn}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 事業所IDが存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenInvalidOfficeId(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when invalid OfficeId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = self::NOT_EXISTING_ID;
        $providedIn = '2021-06';

        $I->sendGet("offices/{$officeId}/home-visit-long-term-care-calc-specs?providedIn={$providedIn}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeVisitLongTermCareCalcSpec(officeId={$officeId}, providedIn={$providedIn}) not found");
    }

    /**
     * サービス提供年月における算定情報が存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenInvalidProvidedIn(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when invalid providedIn');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->homeVisitLongTermCareCalcSpecs[4]->officeId;
        $providedIn = '1960-06';

        $I->sendGet("offices/{$officeId}/home-visit-long-term-care-calc-specs?providedIn={$providedIn}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeVisitLongTermCareCalcSpec(officeId={$officeId}, providedIn={$providedIn}) not found");
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
        $officeId = $this->examples->homeVisitLongTermCareCalcSpecs[4]->officeId;
        $providedIn = '2021-06';

        $I->sendGet("offices/{$officeId}/home-visit-long-term-care-calc-specs?providedIn={$providedIn}");
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
