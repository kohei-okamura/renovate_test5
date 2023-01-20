<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OfficeGroup;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Office\OfficeGroup;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup getIndex のテスト
 * GET /offices-group
 */
class GetIndexOfficeGroupCest extends OfficeGroupTest
{
    use ExamplesConsumer;

    // tests

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
        $expected = Seq::fromArray($this->examples->officeGroups)
            ->filter(fn (OfficeGroup $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (OfficeGroup $x): int => $x->sortOrder)
            ->map(fn (OfficeGroup $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('office-groups');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'sortOrder');
    }

    /**
     * ソート指定テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortById(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->officeGroups)
            ->filter(fn (OfficeGroup $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (OfficeGroup $x): int => $x->id)
            ->map(fn (OfficeGroup $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('office-groups', ['sortBy' => 'id']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 権限範囲が office の場合、空の結果が返るテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithEmptyResultWhenRoleScopeIsOffice(ApiTester $I)
    {
        $I->wantTo('succeed api call with empty result when role scope is office');

        $staff = $this->examples->staffs[33];
        $I->actingAs($staff);

        $I->sendGET('office-groups');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson([], 0, 10, 'sortOrder');
    }

    /**
     * 権限範囲が group の場合、id でフィルタされるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithFileterdByIdWhenRoleScopeIsGroup(ApiTester $I)
    {
        $I->wantTo('succeed api call with fileterd by id when role scope is group');

        $staff = $this->examples->staffs[4];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->officeGroups)
            ->filter(fn (OfficeGroup $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (OfficeGroup $x): bool => in_array($x->id, $this->examples->staffs[4]->officeGroupIds, true))
            ->sortBy(fn (OfficeGroup $x): int => $x->id)
            ->map(fn (OfficeGroup $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('office-groups', ['sortBy' => 'id']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
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

        $I->sendGET('office-groups', ['sortBy' => 'id']);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
