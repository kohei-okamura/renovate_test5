<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\VisitingCareForPwsdCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * HomeHelpServiceCalcSpec Get のテスト.
 * GET /offices/{userId}/visiting-care-for-pwsd-calc-specs/{id}
 */
class GetVisitingCareForPwsdCalcSpecCest extends VisitingCareForPwsdCalcSpecTest
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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[1];
        $expected = $this->domainToArray(compact('visitingCareForPwsdCalcSpec'));

        $I->sendGET("offices/{$visitingCareForPwsdCalcSpec->officeId}/visiting-care-for-pwsd-calc-specs/{$visitingCareForPwsdCalcSpec->id}", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseJson($expected);
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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[1];
        $officeId = self::NOT_EXISTING_ID;

        $I->sendGET("offices/{$officeId}/visiting-care-for-pwsd-calc-specs/{$visitingCareForPwsdCalcSpec->id}", $this->domainToArray($visitingCareForPwsdCalcSpec));

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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[1];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("offices/{$visitingCareForPwsdCalcSpec->officeId}/visiting-care-for-pwsd-calc-specs/{$id}", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "VisitingCareForPwsdCalcSpec({$id}) not found");
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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[0];

        $I->sendGET("offices/{$office->id}/visiting-care-for-pwsd-calc-specs/{$visitingCareForPwsdCalcSpec->id}", $this->domainToArray($visitingCareForPwsdCalcSpec));
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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[0];

        $I->sendGET("offices/{$office->id}/visiting-care-for-pwsd-calc-specs/{$visitingCareForPwsdCalcSpec->id}", $this->domainToArray($visitingCareForPwsdCalcSpec));

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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[1];

        $I->sendGET("offices/{$office->id}/visiting-care-for-pwsd-calc-specs/{$visitingCareForPwsdCalcSpec->id}", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "VisitingCareForPwsdCalcSpec({$visitingCareForPwsdCalcSpec->id}) not found");
    }
}
