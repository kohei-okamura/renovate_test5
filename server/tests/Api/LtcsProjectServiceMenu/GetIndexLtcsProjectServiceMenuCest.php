<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProjectServiceMenu;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Project\LtcsProjectServiceMenu;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProjectServiceMenu getIndex のテスト
 * GET /ltcs-project-service-menus
 */
class GetIndexLtcsProjectServiceMenuCest extends LtcsProjectServiceMenuTest
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

        $expected = Seq::fromArray($this->examples->ltcsProjectServiceMenus)
            ->sortBy(fn (LtcsProjectServiceMenu $x): int => $x->id)
            ->map(fn (LtcsProjectServiceMenu $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/ltcs-project-service-menus');

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

        $expected = Seq::fromArray($this->examples->ltcsProjectServiceMenus)
            ->sortBy(fn (LtcsProjectServiceMenu $x): int => $x->id)
            ->map(fn (LtcsProjectServiceMenu $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/ltcs-project-service-menus?all=true');

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
