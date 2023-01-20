<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Session;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Permission\Permission;
use Lib\Json;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Session get のテスト.
 * GET /session/my
 */
class GetSessionCest extends Test
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

        $I->sendPOST('sessions', [
            'email' => $this->examples->staffs[0]->email,
            'password' => 'PassWoRD',
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->setCookieFromResponse();
        $expected = Json::encode([
            'auth' => [
                'isSystemAdmin' => true,
                'permissions' => Permission::all(),
                'staff' => $this->examples->staffs[0],
            ],
        ]);

        $I->sendGET('sessions/my');

        $I->seeResponseCodeIs(HttpCode::OK);
        // NOTE seeResponseContainsJson だと何故かコケるため、seeResponseEquals で実装.
        $I->seeResponseEquals($expected);
        $I->seeLogCount(0);
    }

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedWithRememberMeToken(ApiTester $I)
    {
        $I->wantTo('succeed with RememberMe Token');

        $staff = $this->examples->staffs[0];
        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
            'rememberMe' => true,
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->setCookieByNameFromResponse('e2e_token');

        $I->sendGET('sessions/my');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'auth' => [
                'staff' => $this->domainToArray($staff),
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * ログインしていない状態の場合401が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithUnauthorized(ApiTester $I)
    {
        $I->wantTo('failed with Unauthorized.');

        $I->sendGET('sessions/my');

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeLogCount(0);
    }
}
