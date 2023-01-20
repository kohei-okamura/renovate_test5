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
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup create のテスト.
 * POST /office-group
 */
class CreateOfficeGroupCest extends OfficeGroupTest
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

        $I->sendPOST('office-groups', $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 親グループを指定して登録するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithParentGroup(ApiTester $I)
    {
        $I->wantTo('succeed API call with parent group.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['parentOfficeGroupId' => $this->examples->officeGroups[0]->id] + $this->defaultParam();

        $I->sendPOST('office-groups', $param);

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 指定した親グループが存在しないと400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenParentGroupNotExists(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when parent group not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['parentOfficeGroupId' => self::NOT_EXISTING_ID] + $this->defaultParam();

        $I->sendPOST('office-groups', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 指定した親グループが他の事業者だった場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenParentGroupIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when parent group is other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['parentOfficeGroupId' => $this->examples->officeGroups[3]->id] + $this->defaultParam();

        $I->sendPOST('office-groups', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['parentOfficeGroupId' => ['正しい値を入力してください。']]]);
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
        $param = $this->defaultParam();

        $I->sendPOST('office-groups', $param);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータ組み立て.
     *
     * @throws \JsonException
     * @return array
     */
    private function defaultParam(): array
    {
        return $this->domainToArray($this->examples->officeGroups[0]);
    }
}
