<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Calling;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Shift\Shift;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Calling shifts のテスト.
 * GET /callings/{token}/shifts
 */
class ShiftCallingCest extends CallingTest
{
    use ExamplesConsumer;

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
        $calling = $this->examples->callings[0];
        $expected = $this->domainToArray(
            Seq::fromArray($this->examples->shifts)
                ->filter(fn (Shift $x): bool => in_array($x->id, $calling->shiftIds, true))
        );

        $I->sendGET("callings/{$this->examples->callings[0]->token}/shifts");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expected);
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

        $I->sendGET("callings/{$token}/shifts");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Calling[{$token}] not found");
    }

    /**
     * Shiftが存在しないと404を返すテスト.
     *
     * @param APiTester $I
     */
    public function failedWithNotFoundWhenIdHaveNoShifts(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID have no shifts');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $calling = $this->examples->callings[3];

        $I->sendGET("callings/{$calling->token}/shifts");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Shift is empty. CallingID={$calling->id}");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET("callings/{$this->examples->callings[0]->token}/shifts");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
