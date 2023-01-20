<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Invitation;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Invitation get のテスト.
 *
 * GET /invitations/{token}
 */
class GetInvitationCest extends InvitationTest
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

        $invitation = $this->examples->invitations[0];
        $expected = $this->domainToArray(compact('invitation'));

        $I->sendGET("invitations/{$invitation->token}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * 存在しないtokenを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfTokenDoesNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if token does not exist.');

        $token = self::NOT_EXISTING_TOKEN;

        $I->sendGET("invitations/{$token}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Invitation({$token}) not found");
    }

    /**
     * 有効期限が過ぎたtokenを指定すると403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenIfTokenHasBeenExpired(ApiTester $I)
    {
        $I->wantTo('fail with Forbidden if token has been expired.');

        $invitation = $this->examples->invitations[4]; // tokenの有効期限が過ぎた招待

        $I->sendGET("invitations/{$invitation->token}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'Token has been expired');
    }
}
