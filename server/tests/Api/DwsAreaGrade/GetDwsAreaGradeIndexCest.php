<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsAreaGrade;

use ApiTester;
use Codeception\Util\HttpCode;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DeaAreaGrade getIndex のテスト.
 * GET /dws-area-grades
 */
class GetDwsAreaGradeIndexCest extends DwsAreaGradeTest
{
    use ExamplesConsumer;

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
        $expected = $this->domainToArray(Seq::fromArray($this->examples->dwsAreaGrades));

        $I->sendGET('dws-area-grades');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }
}
