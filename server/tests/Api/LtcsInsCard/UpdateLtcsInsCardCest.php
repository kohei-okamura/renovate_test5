<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsInsCard;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsInsCard Update のテスト.
 * PUT /users/{userId}/ltcs-ins-card
 */
class UpdateLtcsInsCardCest extends LtcsInsCardTest
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

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];

        $I->sendPUT("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}", $this->domainToArray($ltcsInsCard));

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険被保険者証が更新されました', [
            'id' => $ltcsInsCard->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected, $actual);
    }

    /**
     * UserIdが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendPUT("users/{$userId}/ltcs-ins-cards/{$ltcsInsCard->id}", $this->domainToArray($ltcsInsCard));

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
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
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $userId = $this->examples->users[14]->id;

        $I->sendPUT("users/{$userId}/ltcs-ins-cards/{$ltcsInsCard->id}", $this->domainToArray($ltcsInsCard));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * IDが存在していないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[10];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$id}", $this->domainToArray($ltcsInsCard));

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsInsCard({$id}) not found");
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
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

        $I->sendPUT("users/{$userId}/ltcs-ins-cards/{$id}", $this->domainToArray($ltcsInsCard));

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
        $ltcsInsCard = $this->examples->ltcsInsCards[0];

        $I->sendPUT("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}", $this->domainToArray($ltcsInsCard));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
