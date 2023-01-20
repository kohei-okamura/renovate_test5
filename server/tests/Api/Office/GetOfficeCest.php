<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Office;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Office\OfficeGroup;
use Lib\Exceptions\NotFoundException;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Office get のテスト
 * GET /offices/{id}
 */
class GetOfficeCest extends OfficeTest
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $I->actingAs($this->examples->staffs[0]);
        $office = $this->examples->offices[25];
        $officeGroup = Seq::fromArray($this->examples->officeGroups)
            ->find(fn (OfficeGroup $x) => $x->id === $office->officeGroupId)
            ->headOption()->getOrElse(function (): void {
                throw new NotFoundException('OfficeGroup not found. Please review the test data.');
            });
        $expected = $this->domainToArray([
            'office' => $office,
            'officeGroup' => $officeGroup,
            'homeHelpServiceCalcSpecs' => [
                $this->examples->homeHelpServiceCalcSpecs[7],
                $this->examples->homeHelpServiceCalcSpecs[8],
                $this->examples->homeHelpServiceCalcSpecs[5],
                $this->examples->homeHelpServiceCalcSpecs[6],
            ],
            'homeVisitLongTermCareCalcSpecs' => [
                $this->examples->homeVisitLongTermCareCalcSpecs[8],
                $this->examples->homeVisitLongTermCareCalcSpecs[9],
                $this->examples->homeVisitLongTermCareCalcSpecs[6],
                $this->examples->homeVisitLongTermCareCalcSpecs[7],
            ],
            'visitingCareForPwsdCalcSpecs' => [
                $this->examples->visitingCareForPwsdCalcSpecs[7],
                $this->examples->visitingCareForPwsdCalcSpecs[8],
                $this->examples->visitingCareForPwsdCalcSpecs[5],
                $this->examples->visitingCareForPwsdCalcSpecs[6],
            ],
        ]);

        $I->sendGET("offices/{$office->id}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);

        // 格納データの並び順の検証
        $homeHelpServiceCalcSpecs = $I->grabDataFromResponseByJsonPath('homeHelpServiceCalcSpecs');
        $I->assertMatchesModelSnapshot($homeHelpServiceCalcSpecs);
        $homeVisitLongTermCareCalcSpecs = $I->grabDataFromResponseByJsonPath('homeVisitLongTermCareCalcSpecs');
        $I->assertMatchesModelSnapshot($homeVisitLongTermCareCalcSpecs);
        $visitingCareForPwsdCalcSpecs = $I->grabDataFromResponseByJsonPath('visitingCareForPwsdCalcSpecs');
        $I->assertMatchesModelSnapshot($visitingCareForPwsdCalcSpecs);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotExist(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not exist.');

        $id = self::NOT_EXISTING_ID;

        $I->actingAs($this->examples->staffs[27]);

        $I->sendGET("offices/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$id}) not found");
    }

    /**
     * 異なる組織のIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not in organization.');

        $id = $this->examples->offices[1]->id;

        $I->actingAs($this->examples->staffs[27]);

        $I->sendGET("/offices/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$id}) not found");
    }

    /**
     * 権限のないOfficeのIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not in permitted office.');

        $id = $this->examples->offices[2]->id;
        $I->actingAs($this->examples->staffs[28]);

        $I->sendGET("/offices/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office({$id}) not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $id = $this->examples->offices[0]->id;

        $I->sendGET("/offices/{$id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
