<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OwnExpenseProgram;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OwnExpenseProgram getIndex のテスト
 * GET /own-expense-programs
 */
class GetIndexOwnExpenseProgramCest extends OwnExpenseProgramTest
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
        $expected = Seq::fromArray($this->examples->ownExpensePrograms)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (OwnExpenseProgram $x): int => $x->id)
            ->map(fn (OwnExpenseProgram $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('own-expense-programs');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('own-expense-programs');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * フィルタ指定テスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWhenSpecifyFilterParams(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify filter params');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->ownExpensePrograms[0]->officeId;
        $expected = Seq::fromArray($this->examples->ownExpensePrograms)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->officeId === $officeId || $x->officeId === null)
            ->sortBy(fn (OwnExpenseProgram $x): int => $x->id)
            ->map(fn (OwnExpenseProgram $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('own-expense-programs', ['officeId' => $officeId]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
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
        $query = $this->examples->ownExpensePrograms[1]->name;
        $expected = Seq::fromArray($this->examples->ownExpensePrograms)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->id === $this->examples->ownExpensePrograms[1]->id)
            ->sortBy(fn (OwnExpenseProgram $x): int => $x->id)
            ->map(fn (OwnExpenseProgram $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('own-expense-programs', ['q' => $query]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 認可された事業所だけ取得できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithPermittedOfficesOnly(ApiTester $I)
    {
        $I->wantTo('succeed API call with permitted Offices only');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->ownExpensePrograms)
            ->filter(fn (OwnExpenseProgram $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(
                fn (OwnExpenseProgram $x): bool => in_array($x->officeId, $staff->officeIds, true)
                    || $x->officeId === null
            )
            ->sortBy(fn (OwnExpenseProgram $x): int => $x->id)
            ->map(fn (OwnExpenseProgram $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('own-expense-programs');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id', ['itemsPerPage' => 10]);
    }
}
