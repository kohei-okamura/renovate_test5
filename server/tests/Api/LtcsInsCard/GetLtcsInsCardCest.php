<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsInsCard;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsInsCard get のテスト.
 * GET /users/{userId}/ltcs-ins-cards/{id}
 */
class GetLtcsInsCardCest extends LtcsInsCardTest
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

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCards = $this->examples->ltcsInsCards[0];
        $expected = $this->domainToArray($ltcsInsCards);

        $I->sendGET("users/{$ltcsInsCards->userId}/ltcs-ins-cards/{$ltcsInsCards->id}");

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

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $id = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsInsCard[{$id}] not found");
    }

    /**
     * IDのデータがユーザと一致しないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserDoesNotExist(ApiTester $I)
    {
        $I->wantTo('failed with not found when user does not exist');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendGET("users/{$userId}/ltcs-ins-cards/{$ltcsInsCard->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * アクセス可能なOfficeと契約がない利用者を指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotInAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $userId = $this->examples->users[1]->id;
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $id = $ltcsInsCard->id;

        $I->sendGET("users/{$userId}/ltcs-ins-cards/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 利用者IDが同じ事業者に存在しない場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->ltcsInsCards[0]->id;
        $userId = $this->examples->users[14]->id;

        $I->sendGET("users/{$userId}/ltcs-ins-cards/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
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
        $ltcsInsCards = $this->examples->ltcsInsCards[0];

        $I->sendGET("users/{$ltcsInsCards->userId}/ltcs-ins-cards/{$ltcsInsCards->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
