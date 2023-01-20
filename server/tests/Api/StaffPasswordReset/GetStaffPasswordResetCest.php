<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\StaffPasswordReset;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * StaffPasswordReset get のテスト.
 * GET /password-resets/{token}
 */
class GetStaffPasswordResetCest extends StaffPasswordResetTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    public const INVALID_TOKEN = 'invalid-hash';

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $reset = $this->examples->staffPasswordResets[4];
        $expected = [
            'staffId' => $reset->staffId,
            'name' => $reset->name->toAssoc(),
            'token' => $reset->token,
            'email' => $reset->email,
        ];

        $I->sendGET("/password-resets/{$reset->token}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * トークンが期限切れの場合に410を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenTokenExpired(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when token expired');

        $reset = $this->examples->staffPasswordResets[5];

        $I->sendGET("/password-resets/{$reset->token}");

        $I->seeResponseCodeIs(HttpCode::GONE);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, "StaffPasswordReset[{$reset->token}] is expired");
    }

    /**
     * トークンが無効の場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidToken(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid token');

        $token = self::INVALID_TOKEN;

        $I->sendGET("/password-resets/{$token}");

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "StaffPasswordReset[{$token}] not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
