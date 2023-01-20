<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Job;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Job get のテスト.
 * GET /jobs/{token}
 */
class GetJobCest extends JobTest
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

        $I->actingAs($this->examples->staffs[0]);
        $job = $this->examples->jobs[0];
        $expected = $this->domainToArray($job);

        $I->sendGET("jobs/{$job->token}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundIfIdNotExist(ApiTester $I)
    {
        $I->wantTo('failed with NotFound if id not exist.');

        $I->actingAs($this->examples->staffs[0]);

        $I->sendGET('jobs/' . self::NOT_EXISTING_TOKEN . '');

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'Job(' . self::NOT_EXISTING_TOKEN . ') not found');
    }

    /**
     * スタッフが異なる場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenStaffIsDifferent(ApiTester $I)
    {
        $I->wantTo('fail when staff is different');

        $I->actingAs($this->examples->staffs[0]);

        $I->sendGET("jobs/{$this->examples->jobs[1]->token}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Job({$this->examples->jobs[1]->token}) not found");
    }
}
