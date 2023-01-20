<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatus;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsBilling getIndex のテスト.
 * GET /dws-billings
 */
class GetDwsBillingIndexCest extends Test
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
        $expected = Seq::fromArray($this->examples->dwsBillings)
            ->filter(fn (DwsBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (DwsBilling $x): int => $x->id)
            ->map(fn (DwsBilling $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('dws-billings');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
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
        $expected = Seq::fromArray($this->examples->dwsBillings)
            ->filter(fn (DwsBilling $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (DwsBilling $x): bool => in_array($x->status, [DwsBillingStatus::checking(), DwsBillingStatus::fixed()], true))
            ->sortBy(fn (DwsBilling $x): int => $x->id)
            ->map(fn (DwsBilling $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('dws-billings', ['statuses' => [DwsBillingStatus::checking()->value(), DwsBillingStatus::fixed()->value()]]);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * 実際にall=trueと指定して動作するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithRealParameters(ApiTester $I)
    {
        $I->wantTo('succeed API call with real parameters');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->dwsBillings)
            ->filter(fn (DwsBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (DwsBilling $x): int => $x->id)
            ->map(fn (DwsBilling $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/dws-billings?all=true', ['sortBy' => 'id']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, count($expected), 'id', ['itemsPerPage' => count($expected)]);
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
        $expected = Seq::fromArray($this->examples->dwsBillings)
            ->filter(fn (DwsBilling $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (DwsBilling $x): bool => in_array($x->office->officeId, $staff->officeIds, true))
            ->sortBy(fn (DwsBilling $x): int => $x->id)
            ->map(fn (DwsBilling $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/dws-billings', ['sortBy' => 'id', 'all' => true]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id', ['itemsPerPage' => count($expected)]);
    }

    /**
     * 日付のフィルタパラメータを指定して正しく動作するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSpecifyFilterParamsOfDate(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify filter params of date');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $start = $this->examples->dwsBillings[0]->transactedIn->subMonth();
        $end = $this->examples->dwsBillings[0]->transactedIn->addMonth();
        $expected = Seq::fromArray($this->examples->dwsBillings)
            ->filter(fn (DwsBilling $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (DwsBilling $x): bool => $x->transactedIn->gte($start))
            ->filter(fn (DwsBilling $x): bool => $x->transactedIn->lte($end))
            ->sortBy(fn (DwsBilling $x): int => $x->id)
            ->map(fn (DwsBilling $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("dws-billings?sortBy=id&start={$start->format('Y-m')}&end={$end->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('dws-billings');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
