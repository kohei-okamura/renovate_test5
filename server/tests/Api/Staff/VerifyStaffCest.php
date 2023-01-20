<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Staff;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Staffs verify のテスト.
 * PUT /staff-verifications/{token}
 */
class VerifyStaffCest extends StaffTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    public const INVALID_TOKEN = 'invalid-hash';

    /**
     * API正常呼び出し テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $verification = $this->examples->staffEmailVerifications[0];

        $I->sendPUT("staff-verifications/{$verification->token}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'スタッフが更新されました', [
            'id' => $verification->staffId,
            'organizationId' => $staff->organizationId,
            'staffId' => '',
        ]);
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

        $I->sendPUT("staff-verifications/{$token}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "StaffEmailVerification[{$token}] not found");
    }

    /**
     * トークンが期限切れの場合に410を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenExpiredToken(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when expired token');

        $token = $this->examples->staffEmailVerifications[1]->token;

        $I->sendPUT("staff-verifications/{$token}");

        $I->seeResponseCodeIs(HttpCode::GONE);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, "StaffEmailVerification[{$token}] is expired");
    }

    /**
     * Tokenが他の事業者のスタッフのものだった場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenTokenHasOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when token has other organization');

        $token = $this->examples->staffEmailVerifications[4]->token;

        $I->sendPUT("staff-verifications/{$token}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "StaffEmailVerification[{$token}] not found");
    }
}
