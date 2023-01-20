<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OfficeGroup;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Lib\Arrays;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup update のテスト.
 * PUT /office-groups/{id}
 */
class UpdateOfficeGroupCest extends OfficeGroupTest
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
        $id = $this->examples->officeGroups[2]->id;
        $officeGroup = $this->examples->officeGroups[2];

        $I->sendPUT("office-groups/{$id}", $this->domainToArray($officeGroup));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが更新されました', [
            'id' => $id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET('/office-groups?all=true');
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 重複した表示順が指定された場合でも更新できるテスト.
     * 表示順を無視して更新を行う.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSortOrderIsNotUnique(ApiTester $I)
    {
        $I->wantTo('succeed api call when sortOrder is not unique');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeGroup = $this->examples->officeGroups[2];

        $I->sendPUT("office-groups/{$officeGroup->id}", $this->domainToArray($officeGroup->copy([
            'sortOrder' => $this->examples->officeGroups[0]->sortOrder,
        ])));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが更新されました', [
            'id' => $officeGroup->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // 更新後のチェック
        $keys = [
            'organizationId',
            'parentOfficeGroupId',
            'name',
            'sortOrder',
            'createdAt',
            'updatedAt',
        ];
        $expected = $officeGroup->copy(['updatedAt' => Carbon::now()]);
        $expectedArray = ['officeGroup' => Arrays::generate(function () use ($keys, $expected): iterable {
            foreach ($this->domainToArray($expected) as $key => $value) {
                if (in_array($key, $keys, true)) {
                    yield $key => $value;
                }
            }
        })];
        $I->sendGET("office-groups/{$officeGroup->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expectedArray);
        $I->seeLogCount(0);
    }

    /**
     * IDが不正の場合の404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when Invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $officeGroup = $this->examples->officeGroups[2];

        $I->sendPUT("office-groups/{$id}", $this->domainToArray($officeGroup));

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

        $I->sendPUT("office-groups/{$officeGroup->id}", $this->domainToArray($officeGroup));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
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
        $officeGroup = $this->examples->officeGroups[0]->copy([
            'parentOfficeGroupId' => 4,
        ]);

        $I->sendPUT("office-groups/{$officeGroup->id}", $this->domainToArray($officeGroup));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['parentOfficeGroupId' => ['正しい値を入力してください。']]]);
    }

    /**
     * 指定したグループが他の事業者だった場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenGroupIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with Not Found when group is other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeGroup = $this->examples->officeGroups[3];

        $I->sendPUT("office-groups/{$officeGroup->id}", $this->domainToArray($officeGroup));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "OfficeGroup({$officeGroup->id}) not found");
    }
}
