<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\StaffPasswordReset;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Organization\Organization;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * StaffPasswordReset update のテスト.
 * PUT /password-resets/{token}
 */
class UpdateStaffPasswordResetCest extends StaffPasswordResetTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $reset = $this->examples->staffPasswordResets[4];
        $organization = Seq::fromArray($this->examples->organizations)
            ->filter(fn (Organization $x): bool => $x->code === 'eustylelab1')
            ->head();

        $I->sendPUT("/password-resets/{$reset->token}", $this->buildParameter('eustylelab'));

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, 'スタッフパスワードが更新されました', [
            'id' => $reset->staffId,
            'organizationId' => $organization->id,
        ]);

        // 新しいパスワードで認証が通ることを検証
        $I->sendPOST('sessions', [
            'email' => $this->examples->staffs[0]->email,
            'password' => 'eustylelab',
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
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

        $I->sendPUT("/password-resets/{$reset->token}", $this->buildParameter('password'));

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

        $token = 'INVALID';

        $I->sendPUT("/password-resets/{$token}", $this->buildParameter('password'));

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "StaffPasswordReset[{$token}] not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * リクエストパラメータの生成.
     *
     * @param string $password
     * @return array
     */
    private function buildParameter(string $password): array
    {
        return ['password' => $password];
    }
}
