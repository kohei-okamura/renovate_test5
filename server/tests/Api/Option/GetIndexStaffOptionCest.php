<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Option;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Option getIndex のテスト
 * GET /options/staffs
 */
class GetIndexStaffOptionCest extends OptionTest
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
        $expected = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Staff $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (Staff $x): array => [
                'text' => $x->name->displayName,
                'value' => $x->id,
            ])
            ->toArray();

        $I->sendGET('/options/staffs', [
            'permission' => Permission::listStaffs()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定した事業所のスタッフのみ取得できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSpecifyOfficeIds(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify officeIds');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeIds = [$this->examples->offices[0]->id, $this->examples->offices[2]->id];
        $expected = $this->expectedFilterByOfficeIds($staff, $officeIds);

        $I->sendGET('/options/staffs', [
            'permission' => Permission::listStaffs()->value(),
            'officeIds' => $officeIds,
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 認可された事業所のスタッフのみ取得できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedApiCallWithOnlyStaffsWhoBelongToTheAuthorizedOffice(ApiTester $I)
    {
        $I->wantTo('succeed API call with only staffs who belong to the authorized office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $expected = $this->expectedFilterByOfficeIds($staff, $staff->officeIds);

        $I->sendGET('/options/staffs', [
            'permission' => Permission::listStaffs()->value(),
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

        $I->sendGET('/options/staffs', [
            'permission' => Permission::listStaffs()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['permission' => ['権限を持っていません。']]]);
    }

    /**
     * @param \Domain\Staff\Staff $staff
     * @param array|int[] $officeIds
     * @return array
     */
    private function expectedFilterByOfficeIds(Staff $staff, array $officeIds): array
    {
        return Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(
                fn (Staff $x): bool => Seq::fromArray($x->officeIds)->exists(
                    fn (int $officeId): bool => in_array($officeId, $officeIds, true)
                )
            )
            ->sortBy(fn (Staff $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (Staff $x): array => [
                'text' => $x->name->displayName,
                'value' => $x->id,
            ])
            ->toArray();
    }
}
