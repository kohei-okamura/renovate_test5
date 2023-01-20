<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Office;

use ApiTester;
use Closure;
use Codeception\Util\HttpCode;
use Domain\Office\Office;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Office getIndex のテスト.
 * GET /offices
 */
class GetOfficeIndexCest extends OfficeTest
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

        $expected = $this->getCommonExpected();

        $I->sendGET('offices');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * ソート指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortById(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Office $x): int => $x->id)
            ->map(fn (Office $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('offices', ['sortBy' => 'id']);

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

        $expected = $this->getCommonExpected(fn (Office $x): bool => $x->id === $this->examples->offices[2]->id);
        $query = $this->examples->offices[2]->phoneticName;

        $I->sendGET('offices', ['q' => $query]);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 複数のstatusを指定した場合のテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenStatusesGiven(ApiTester $I)
    {
        $I->wantTo('succeed api call when statuses given');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $expected = $this->getCommonExpected(
            fn (Office $x): bool => in_array($x->status, [OfficeStatus::inPreparation(), OfficeStatus::inOperation()], true)
        );
        $query = '?status[]=' . OfficeStatus::inPreparation() . '&status[]=' . OfficeStatus::inOperation() . '&all=true';

        $I->sendGET("offices{$query}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->assertSame($expected, $I->grabResponseArray()['list']);
    }

    /**
     * 事業所区分を指定した場合のテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenPurposeGiven(ApiTester $I)
    {
        $I->wantTo('succeed api call when purpose is given');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $expected = $this->getCommonExpected(fn (Office $x): bool => $x->purpose === Purpose::external());
        $query = '?purpose=' . Purpose::external()->value() . '&all=true';

        $I->sendGET("offices{$query}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->assertSame($expected, $I->grabResponseArray()['list']);
    }

    /**
     * ページ番号指定テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithPageParam(ApiTester $I)
    {
        $I->wantTo('succeed API call with Page Param');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (Office $x): int => $x->id)
            ->map(fn (Office $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('offices?page=1&sortBy=id&all=false');

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
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $staff->organizationId)
            ->sortBy(fn (Office $x): int => $x->id)
            ->map(fn (Office $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/offices?all=true', ['sortBy' => 'id']);

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
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Office $x): bool => in_array($x->id, $staff->officeIds, true))
            ->sortBy(fn (Office $x): int => $x->id)
            ->map(fn (Office $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/offices', ['sortBy' => 'id', 'all' => true]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id', ['itemsPerPage' => 1]);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('offices');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * ページ番号が文字列のとき400を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenStringPage(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when string page');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendGET('offices', ['page' => 'X']);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * よく使う expected を返す.
     *
     * @param null|\Closure $filter 追加で実行する filter 関数
     * @return array
     */
    private function getCommonExpected(?Closure $filter = null): array
    {
        $seq = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId);
        if (!empty($filter)) {
            $seq = $seq->filter($filter);
        }
        return $seq
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => Json::decode(Json::encode($x), true))
            ->toArray();
    }
}
