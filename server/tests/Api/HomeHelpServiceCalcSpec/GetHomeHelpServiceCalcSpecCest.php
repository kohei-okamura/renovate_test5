<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\HomeHelpServiceCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * HomeHelpServiceCalcSpec Get のテスト.
 * GET /offices/{userId}/home-help-service-calc-specs/{id}
 */
class GetHomeHelpServiceCalcSpecCest extends HomeHelpServiceCalcSpecTest
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
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[0];

        $I->sendGET("offices/{$this->examples->offices[0]->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($this->domainToArray($homeHelpServiceCalcSpec));

        $I->seeLogCount(0);
    }

    /**
     * OfficeIDが存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidOfficeId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid OfficeId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[1];
        $officeId = self::NOT_EXISTING_ID;

        $I->sendGET("offices/{$officeId}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$officeId}] is not found");
    }

    /**
     * IDが存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("offices/{$this->examples->offices[0]->id}/home-help-service-calc-specs/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeHelpServiceCalcSpec({$id}) not found");
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
        $office = $this->examples->offices[0];
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[0];

        $I->sendGET("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * IDの事業者が異なっていると404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[1];
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[0];

        $I->sendGET("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$office->id}] is not found");
    }

    /**
     * IDがOfficeに紐づいていないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotBelongsToOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not belongs to Office');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[0];
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[1];

        $I->sendGET("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeHelpServiceCalcSpec({$homeHelpServiceCalcSpec->id}) not found");
    }
}
