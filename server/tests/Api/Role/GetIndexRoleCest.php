<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Role;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Role\Role;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Role getIndex のテスト.
 * GET /role
 */
class GetIndexRoleCest extends RoleTest
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
        $expected = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Role $x): int => $x->sortOrder)
            ->map(fn (Role $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('roles');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'sortOrder');
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
        $expected = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Role $x): int => $x->id)
            ->map(fn (Role $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('roles', ['sortBy' => 'id']);

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

        $I->sendGET('roles');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
