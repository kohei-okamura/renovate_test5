<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Calling;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Calling acknowledge のテスト.
 * POST /callings/{token}/acknowledge
 */
class AcknowledgeCallingCest extends CallingTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param APiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST("callings/{$this->examples->callings[0]->token}/acknowledges");

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '出勤確認応答を登録しました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => '*',
        ]);
    }

    /**
     * Tokenが有効期限切れだと410を返すテスト.
     *
     * @param APiTester $I
     */
    public function failedWithForbbidenWhenTokenExipred(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when Token expired');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $token = $this->examples->callings[2]->token;

        $I->sendPOST("callings/{$token}/acknowledges");

        $I->seeResponseCodeIs(HttpCode::GONE);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, "Calling[{$token}] is expired");
    }

    /**
     * Tokenが存在しないと404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidToken(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid Token');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $token = 'INVALID_TOKEN';

        $I->sendPOST("callings/{$token}/acknowledges");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Calling[{$token}] not found");
    }

    /**
     * 出勤確認のStaffIdと一致しないスタッフの操作の場合、404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenOtherStaffOperate(ApiTester $I)
    {
        $I->wantTo('fail with not found when other staff operate');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $token = $this->examples->callings[1]->token;

        $I->sendPOST("callings/{$token}/acknowledges");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Calling[{$token}] not found");
    }
}
