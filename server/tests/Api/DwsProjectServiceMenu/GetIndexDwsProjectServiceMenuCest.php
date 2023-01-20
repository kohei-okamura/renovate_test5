<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProjectServiceMenu;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Project\DwsProjectServiceMenu;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProjectServiceMenu getIndex のテスト
 * GET /dws-project-service-menus
 */
class GetIndexDwsProjectServiceMenuCest extends DwsProjectServiceMenuTest
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

        $expected = Seq::fromArray($this->examples->dwsProjectServiceMenus)
            ->sortBy(fn (DwsProjectServiceMenu $x): int => $x->id)
            ->map(fn (DwsProjectServiceMenu $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('/dws-project-service-menus');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * all=trueと指定して動作するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSpecifyAll(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify all');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $expected = Seq::fromArray($this->examples->dwsProjectServiceMenus)
            ->sortBy(fn (DwsProjectServiceMenu $x): int => $x->id)
            ->map(fn (DwsProjectServiceMenu $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/dws-project-service-menus?all=true');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson(
            $expected,
            0,
            count($expected),
            'id',
            ['itemsPerPage' => count($expected)]
        );
    }
}
