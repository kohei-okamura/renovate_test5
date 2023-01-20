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
 * LtcsInsCard Create のテスト.
 * POST /users/{userId}/ltcs-ins-card
 */
class CreateLtcsInsCardCest extends LtcsInsCardTest
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

        $I->sendPOST("users/{$this->examples->users[0]->id}/ltcs-ins-cards", $this->domainToArray($ltcsInsCard));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険被保険者証が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => '*',
        ]);
    }

    /**
     * 利用者が存在しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/ltcs-ins-cards", $this->domainToArray($ltcsInsCard));

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
        $ltcsInsCard = $this->examples->ltcsInsCards[0];
        $userId = $this->examples->users[14]->id;

        $I->sendPOST("users/{$userId}/ltcs-ins-cards", $this->domainToArray($ltcsInsCard));

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

        $I->sendPOST(
            "users/{$userId}/ltcs-ins-cards",
            $this->domainToArray($this->examples->ltcsInsCards[0]),
        );

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

        $I->sendPOST("users/{$this->examples->users[0]->id}/ltcs-ins-cards", $this->domainToArray($ltcsInsCard));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
