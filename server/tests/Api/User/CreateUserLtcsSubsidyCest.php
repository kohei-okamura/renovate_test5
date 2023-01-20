<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\User;

use ApiTester;
use Codeception\Util\HttpCode;
use Lib\Json;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserLtcsSubsidies create のテスト.
 * POST /users/{userId}/ltcs-subsidies
 */
class CreateUserLtcsSubsidyCest extends UserLtcsSubsidyTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('/users/' . $this->examples->users[0]->id . '/ltcs-subsidies', $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '公費情報が登録されました', [
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
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/ltcs-subsidies", $this->defaultParam());

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
        $userId = $this->examples->users[14]->id;

        $I->sendPOST("users/{$userId}/ltcs-subsidies", $this->defaultParam());

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
            "users/{$userId}/ltcs-subsidies",
            $this->defaultParam(),
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

        $I->sendPOST("/users/{$this->examples->users[0]->id}/ltcs-subsidies", $this->defaultParam());

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータの組み立て.
     *
     * @throws \JsonException
     * @return array
     */
    private function defaultParam(): array
    {
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        return [
            'period' => Json::decode(Json::encode($userLtcsSubsidy->period), true),
            'defrayerCategory' => $userLtcsSubsidy->defrayerCategory->value(),
            'defrayerNumber' => $userLtcsSubsidy->defrayerNumber,
            'recipientNumber' => $userLtcsSubsidy->recipientNumber,
            'benefitRate' => $userLtcsSubsidy->benefitRate,
            'copay' => $userLtcsSubsidy->copay,
        ];
    }
}
