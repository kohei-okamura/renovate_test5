<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Staff\Staff;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staff getIndex のテスト.
 * GET /staffs
 */
class GetStaffIndexCest extends StaffTest
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
        $expected = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Staff $s): int => $s->id)
            ->map(fn (Staff $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        // デフォルトのソート順(name)だと$expectedが作れないので、idを指定
        $I->sendGET('staffs', ['sortBy' => 'id']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * フィルタ指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSearchParameter(ApiTester $I)
    {
        $I->wantTo('succeed API Call with search parameter');

        $staff = $this->examples->staffs[2];
        $I->actingAs($staff);

        $officeId = $staff->officeIds[0];
        $status = $staff->status;

        $expected = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Staff $x): bool => in_array($officeId, $x->officeIds, true))
            ->filter(fn (Staff $x): bool => $x->status === $status)
            ->sortBy(fn (Staff $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (Staff $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('staffs', ['officeId' => $officeId, 'status' => [$status->value()]]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * クエリ指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSearchQuery(ApiTester $I)
    {
        $I->wantTo('succeed API Call with search query');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $query = str_replace([' '], '', $staff->name->phoneticDisplayName);
        $expected = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Staff $x): bool => $x->id === $staff->id)
            ->sortBy(fn (Staff $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (Staff $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('staffs', ['q' => $query]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 認可された事業所に属するスタッフのみ取得できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithPermittedStaffsOnly(ApiTester $I)
    {
        $I->wantTo('succeed API call with permitted Staffs only');

        $staff = $this->examples->staffs[28]; // officeIds は1のみ
        $I->actingAs($staff);

        $permittedOfficeId = $staff->officeIds[0]; // 1つしかないので、[0]でとっちゃう。
        $expected = Seq::fromArray($this->examples->staffs)
            ->filter(fn (Staff $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Staff $x): bool => in_array($permittedOfficeId, $x->officeIds, true))
            ->sortBy(fn (Staff $x): int => $x->id)
            ->map(fn (Staff $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/staffs', ['sortBy' => 'id', 'itemsPerPage' => 10]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
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

        $I->sendGET('/staffs', ['sortBy' => 'id', 'itemsPerPage' => 10]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
