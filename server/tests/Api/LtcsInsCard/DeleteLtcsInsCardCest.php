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
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsInsCard delete のテスト.
 * DELETE /users/{userId}/ltcs-ins-cards/{id}
 */
class DeleteLtcsInsCardCest extends LtcsInsCardTest
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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];

        $I->sendDELETE("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}");

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険被保険者証が削除されました', [
            'organizationId' => $staff->organizationId,
            //            'staffId' => $staff->id, // TODO 認可の対応で実装する
            'id' => '*',
        ]);
    }

    /**
     * 請求情報に紐づく利用者の介護保険被保険者証を指定すると400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestIfLtcsInsCardBelongToBilling(ApiTester $I)
    {
        $I->wantTo('fail with bad request if ltcs ins card belong to billing');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];

        $I->sendDELETE("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}");
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['指定した介護保険被保険者証に紐づく請求情報が存在しています。']]]);
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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];
        $id = self::NOT_EXISTING_ID;

        $I->sendDELETE("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(0, LogLevel::WARNING, "LtcsInsCard({$id}) not found");
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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];
        $userId = self::NOT_EXISTING_ID;

        $I->sendDELETE("users/{$userId}/ltcs-ins-cards/{$ltcsInsCard->id}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];
        $id = $ltcsInsCard->id;

        $I->sendDELETE("users/{$userId}/ltcs-ins-cards/{$id}");

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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];
        $userId = $this->examples->users[14]->id;
        $id = $ltcsInsCard->id;

        $I->sendDELETE("users/{$userId}/ltcs-ins-cards/{$id}");

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
        $ltcsInsCard = $this->examples->ltcsInsCards[10];

        $I->sendDELETE("users/{$ltcsInsCard->userId}/ltcs-ins-cards/{$ltcsInsCard->id}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
