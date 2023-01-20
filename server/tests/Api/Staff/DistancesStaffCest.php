<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Location;
use Domain\Staff\Staff;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staff distances のテスト.
 * GET /staffs/distances
 */
class DistancesStaffCest extends StaffTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * 距離をどのように計測すれば良いかわからないところから、異常値であるlat=0, lng=0 （大西洋の上）にいるスタッフを作成し、
     * 同じくlat=0, lng=0 の位置からdistance=0にいる（＝同じ位置の人が出る）人を探せたかで、テストを実施しています。
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filterParams = [
            'location' => $this->domainToArray(Location::create(['lat' => 0, 'lng' => 0])),
            'range' => 0,
        ];
        $paginationParams = [
            'itemsPerPage' => 10,
        ];
        $expected = [
            [
                'distance' => 0,
                'destination' => $this->domainToArray($this->examples->staffs[27]),
            ],
        ];

        $I->sendGET('staffs/distances', $filterParams + $paginationParams);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'distance');
    }

    /**
     * ソート指定 テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortById(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by distance');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filterParams = [
            'location' => $this->domainToArray(Location::create(['lat' => 35.689680, 'lng' => 139.692095])), // 東京都庁
            'range' => 3000,
        ];
        $paginationParams = [
            'itemsPerPage' => 10,
            'sortBy' => 'id',
        ];

        $I->sendGET('staffs/distances', $filterParams + $paginationParams);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $response = $I->grabResponseArray();
        foreach ($response['list'] as $index => $entity) {
            if ($index > 0) {
                assertTrue($entity['destination']['id'] > $response['list'][$index - 1]['destination']['id']);
            }
        }
    }

    /**
     * 事業者IDで絞られているテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSameOrganization(ApiTester $I)
    {
        $I->wantTo('succeed API call with same Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filterParams = [
            // 複数件結果を取得できる検索条件
            'location' => $this->domainToArray(Location::create(['lat' => 35.689680, 'lng' => 139.692095])), // 東京都庁
            'range' => 3000,
        ];
        $paginationParams = [
            'itemsPerPage' => 10,
        ];
        $staffSeq = Seq::fromArray($this->examples->staffs);

        $I->sendGET('staffs/distances', $filterParams + $paginationParams);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $response = $I->grabResponseArray();
        foreach ($response['list'] as $index => $entity) {
            $staffId = $entity['destination']['id'];
            /** @var \Domain\Staff\Staff $exampleStaff */
            $exampleStaff = $staffSeq->find(fn (Staff $x): bool => $x->id === $staffId)->orNull();
            assertNotNull($exampleStaff);
            assertEquals($staff->organizationId, $exampleStaff->organizationId);
        }
    }

    /**
     * 認可された事業所に属するスタッフのみ取得できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithPermittedStaffs(ApiTester $I)
    {
        $I->wantTo('succeed API call with permitted Staffs');

        $staff = $this->examples->staffs[28]; // officeIds は1のみ
        $I->actingAs($staff);
        $filterParams = [
            // 複数件結果を取得できる検索条件
            'location' => $this->domainToArray(Location::create(['lat' => 35.689680, 'lng' => 139.692095])), // 東京都庁
            'range' => 3000,
        ];
        $paginationParams = [
            'itemsPerPage' => 10,
        ];

        $I->sendGET('staffs/distances', $filterParams + $paginationParams);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $response = $I->grabResponseArray();
        foreach ($response['list'] as $entity) {
            $officeIds = $entity['destination']['officeIds'];
            assertTrue(in_array(1, $officeIds, true));
        }
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
        $filterParams = [
            'location' => $this->domainToArray(Location::create(['lat' => 35.689680, 'lng' => 139.692095])), // 東京都庁
            'range' => 3000,
        ];
        $paginationParams = [
            'itemsPerPage' => 10,
            'sortBy' => 'id',
        ];

        $I->sendGET('staffs/distances', $filterParams + $paginationParams);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
