<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Status;

use ApiTester;
use Codeception\Util\HttpCode;
use Tests\Api\Test;

/**
 * Status getのテスト.
 * GET /status
 */
final class GetStatusCest extends Test
{
    /**
     * API正常呼び出しテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $I->sendGet('status');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
    }
}
