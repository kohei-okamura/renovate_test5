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
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * VisitingCareForPwsdCalcSpec Create のテスト.
 * POST /offices/{userId}/visiting-care-for-pwsd-calc-specs
 */
class CreateVisitingCareForPwsdCalcSpecCest extends VisitingCareForPwsdCalcSpecTest
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
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[0];

        $I->sendPOST("offices/{$this->examples->offices[0]->id}/visiting-care-for-pwsd-calc-specs", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所算定情報（障害・重度訪問介護）が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 事業所が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenOfficeIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when officeId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $visitingCareForPwsdCalcSpec = $this->examples->visitingCareForPwsdCalcSpecs[0];
        $officeId = self::NOT_EXISTING_ID;

        $I->sendPOST("offices/{$officeId}/visiting-care-for-pwsd-calc-specs", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$officeId}] is not found");
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

        $I->sendPOST("offices/{$office->id}/visiting-care-for-pwsd-calc-specs", $this->domainToArray($visitingCareForPwsdCalcSpec));

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

        $I->sendPOST("offices/{$office->id}/visiting-care-for-pwsd-calc-specs", $this->domainToArray($visitingCareForPwsdCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$office->id}] is not found");
    }
}
