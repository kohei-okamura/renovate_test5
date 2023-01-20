<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\HomeHelpServiceCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * HomeHelpServiceCalcSpec Update のテスト.
 * PUT /offices/{userId}/home-help-service-calc-specs
 */
class UpdateHomeHelpServiceCalcSpecCest extends HomeHelpServiceCalcSpecTest
{
    use ExamplesConsumer;
    use TransactionMixin;

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

        $I->sendPUT("offices/{$this->examples->offices[0]->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}", $this->domainToArray($homeHelpServiceCalcSpec));

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所算定情報（障害・居宅介護）が更新されました', [
            'id' => $homeHelpServiceCalcSpec->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("offices/{$this->examples->offices[0]->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
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

        $I->sendPUT("offices/{$officeId}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}", $this->domainToArray($homeHelpServiceCalcSpec));

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
        $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[1];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("offices/{$this->examples->offices[0]->id}/home-help-service-calc-specs/{$id}", $this->domainToArray($homeHelpServiceCalcSpec));

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

        $I->sendPUT("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}", $this->domainToArray($homeHelpServiceCalcSpec));
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

        $I->sendPUT("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}", $this->domainToArray($homeHelpServiceCalcSpec));

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

        $I->sendPUT("offices/{$office->id}/home-help-service-calc-specs/{$homeHelpServiceCalcSpec->id}", $this->domainToArray($homeHelpServiceCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeHelpServiceCalcSpec({$homeHelpServiceCalcSpec->id}) not found");
    }
}
