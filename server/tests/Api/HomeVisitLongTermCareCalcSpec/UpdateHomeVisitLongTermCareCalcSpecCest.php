<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\HomeVisitLongTermCareCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\ProvisionReport\LtcsProvisionReport;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * HomeVisitLongTermCareCalcSpec Update のテスト.
 * PUT /offices/{userId}/home-visiting-long-term-care-calc-spec/{id}
 */
class UpdateHomeVisitLongTermCareCalcSpecCest extends HomeVisitLongTermCareCalcSpecTest
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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[1];

        $I->sendPUT(
            "offices/{$homeVisitLongTermCareCalcSpec->officeId}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}",
            $this->domainToArray($homeVisitLongTermCareCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所算定情報（介保・訪問介護）が更新されました', [
            'id' => $homeVisitLongTermCareCalcSpec->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("offices/{$homeVisitLongTermCareCalcSpec->officeId}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        /** @var \Domain\Office\HomeVisitLongTermCareCalcSpec $updatedHomeVisitLongTermCareCalcSpec */
        $expected = $I->grabResponseArray() + [
            'provisionReportCount' => Seq::fromArray($this->examples->ltcsProvisionReports)
                ->filter(
                    fn (LtcsProvisionReport $x) => $x->providedIn->between($homeVisitLongTermCareCalcSpec->period->start, $homeVisitLongTermCareCalcSpec->period->end)
                )->count(),
        ];
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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[1];
        $officeId = self::NOT_EXISTING_ID;

        $I->sendPUT("offices/{$officeId}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}", $this->domainToArray($homeVisitLongTermCareCalcSpec));

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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[1];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("offices/{$homeVisitLongTermCareCalcSpec->officeId}/home-visit-long-term-care-calc-specs/{$id}", $this->domainToArray($homeVisitLongTermCareCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeVisitLongTermCareCalcSpec({$id}) not found");
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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];

        $I->sendPUT("offices/{$office->id}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}", $this->domainToArray($homeVisitLongTermCareCalcSpec));

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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];

        $I->sendPUT("offices/{$office->id}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}", $this->domainToArray($homeVisitLongTermCareCalcSpec));

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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[1];

        $I->sendPUT("offices/{$office->id}/home-visit-long-term-care-calc-specs/{$homeVisitLongTermCareCalcSpec->id}", $this->domainToArray($homeVisitLongTermCareCalcSpec));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "HomeVisitLongTermCareCalcSpec({$homeVisitLongTermCareCalcSpec->id}) not found");
    }
}
