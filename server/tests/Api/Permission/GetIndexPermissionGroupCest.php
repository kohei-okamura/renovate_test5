<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Permission;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Permission\PermissionGroup;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * PermissionGroup getIndex のテスト.
 * GET /permissions
 */
class GetIndexPermissionGroupCest extends PermissionGroupTest
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
        $expected = Seq::fromArray($this->examples->permissionGroups)
            ->sortBy(fn (PermissionGroup $x): int => $x->sortOrder)
            ->map(fn (PermissionGroup $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('permissions');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'sortOrder');
        $I->seeLogCount(0);
    }

    /**
     * ソート指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortByiId(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->permissionGroups)
            ->sortBy(fn (PermissionGroup $x): int => $x->id)
            ->map(fn (PermissionGroup $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('permissions', ['sortBy' => 'id']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }
}
