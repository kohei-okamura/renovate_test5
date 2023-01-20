<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\ServiceCodeDictionary;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * GetIndexLtcsHomeVisitLongTermCareDictionaryEntry テスト.
 */
class GetIndexLtcsHomeVisitLongTermCareDictionaryEntryCest extends Test
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
        $officeId = $this->examples->homeVisitLongTermCareCalcSpecs[4]->officeId;
        $effectivatedOn = $this->examples->ltcsHomeVisitLongTermCareDictionaries[2]->effectivatedOn->format(('Y-m-d'));

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary?officeId={$officeId}&isEffectiveOn={$effectivatedOn}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
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
        $officeId = $this->examples->homeVisitLongTermCareCalcSpecs[4]->officeId;
        $isEffectiveOn = $this->examples->ltcsHomeVisitLongTermCareDictionaries[2]->effectivatedOn->format(('Y-m-d'));
        $timeframe = Timeframe::midnight()->value();
        $physicalMinutes = 250;
        $houseworkMinutes = 90;
        $headcount = 2;
        $q = '112444';
        $query = $this->buildQueryString(compact(
            'officeId',
            'isEffectiveOn',
            'q',
            'timeframe',
            'physicalMinutes',
            'houseworkMinutes',
            'headcount',
        ));

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary{$query}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    /**
     * カテゴリ指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithCategory(ApiTester $I)
    {
        $I->wantTo('succeed API Call with Category');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->homeVisitLongTermCareCalcSpecs[3]->officeId;
        $category = LtcsProjectServiceCategory::physicalCare()->value();
        $effectivatedOn = $this->examples->ltcsHomeVisitLongTermCareDictionaries[2]->effectivatedOn->format(('Y-m-d'));

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary?officeId={$officeId}&isEffectiveOn={$effectivatedOn}&category={$category}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
}
