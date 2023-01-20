<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Session;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Session delete のテスト.
 * DELETE /sessions
 */
class DeleteSessionCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * API正常呼び出し テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
        ]);
        $I->setCookieFromResponse();

        $I->sendDELETE('sessions');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::INFO,
            'スタッフがログアウトしました',
            ['organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
    }

    /**
     * セッション削除後に、認証が無効になっているテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedUnauthorizedAfterSessionDelete(ApiTester $I)
    {
        $I->wantTo('succeed unauthroized after session delete.');

        $staff = $this->examples->staffs[0];
        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
        ]);
        $I->setCookieFromResponse();

        $I->sendDELETE('sessions');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::INFO,
            'スタッフがログアウトしました',
            ['organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
        $I->setCookieFromResponse();
        $I->sendGET('offices');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * セッション削除後に、rememberMeクッキーも削除されている確認
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedRemoveRememberMeCookie(ApiTester $I)
    {
        $I->wantTo('succeed remove rememberMe Cookie');

        $staff = $this->examples->staffs[0];
        $I->sendPOST('sessions', [
            'email' => $staff->email,
            'password' => 'PassWoRD',
            'rememberMe' => true,
        ]);
        $I->setCookieFromResponse();
        $I->seeSetCookie('e2e_token');

        $I->sendDELETE('sessions');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(2);
        $I->seeLogMessage(
            0,
            LogLevel::INFO,
            'スタッフがログアウトしました',
            ['organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
        $I->seeLogMessage(
            1,
            LogLevel::INFO,
            'スタッフリメンバートークンが削除されました',
            ['id' => '*', 'organizationId' => $staff->organizationId, 'staffId' => $staff->id]
        );
        $I->dontSeeSetCookie('e2e_token');
    }

    /**
     * セッションがない場合に失敗するテスト.
     *
     * @param ApiTester $I
     */
    public function failedDeletingWhenNoSession(ApiTester $I)
    {
        $I->wantTo('failed deleting when no session');

        $I->sendDELETE('sessions');

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeLogCount(0);
    }
}
