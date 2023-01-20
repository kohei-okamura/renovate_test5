<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Option;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Permission\Permission;
use Domain\Role\Role;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Option getIndex のテスト
 * GET /options/roles
 */
class GetIndexRoleOptionCest extends OptionTest
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

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->roles)
            ->filter(fn (Role $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Role $x): int => $x->sortOrder)
            ->map(fn (Role $x): array => [
                'text' => $x->name,
                'value' => $x->id,
            ])
            ->toArray();

        $I->sendGET('/options/roles', [
            'permission' => Permission::listRoles()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定された権限を持っていない場合、400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenSpecifyUnauthorizedPermission(ApiTester $I)
    {
        $I->wantTo('fail with bad request when specify unauthorized permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('/options/roles', [
            'permission' => Permission::listRoles()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['permission' => ['権限を持っていません。']]]);
    }
}
