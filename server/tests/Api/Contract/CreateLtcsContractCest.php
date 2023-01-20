<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Contract;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsContract create のテスト.
 * POST /ltcs-contracts
 */
class CreateLtcsContractCest extends ContractTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('users/' . $this->examples->users[5]->id . '/ltcs-contracts', $this->domainToArray($this->examples->contracts[0]));

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * OfficeIDが存在せずBAD REQUESTが返るテスト
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdNotExists(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when office_id not exists.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['officeId' => self::NOT_EXISTING_ID] + $this->domainToArray($this->examples->contracts[0]);

        $I->sendPOST("users/{$this->examples->users[0]->id}/ltcs-contracts", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 異なる事業者のofficeIdの場合400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when officeId is outside organization.');

        $staff = $this->examples->staffs[30]; // 管理者
        $I->actingAs($staff);
        $userId = $this->examples->contracts[1]->userId;
        $officeId = $this->examples->offices[1]->id; // 異なる事業者の事業所ID
        $param = ['officeId' => $officeId] + $this->domainToArray($this->examples->contracts[1]);

        $I->sendPOST("users/{$userId}/ltcs-contracts", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能でないOfficeIdの場合400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('failed with Bad Request when OfficeId is not accessible');

        $staff = $this->examples->staffs[28]; // 事業所管理者
        $I->actingAs($staff);
        $userId = $this->examples->contracts[1]->userId;
        $officeId = $this->examples->offices[2]->id; // 事業者は同じだが別の事業所のID
        $param = ['officeId' => $officeId] + $this->domainToArray($this->examples->contracts[1]);

        $I->sendPOST("users/{$userId}/ltcs-contracts", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * UserIDが存在せずに404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidUserId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when invalid UserID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = self::NOT_EXISTING_ID;

        $I->sendPOST("users/{$userId}/ltcs-contracts", $this->domainToArray($this->examples->contracts[0]));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 事業者が異なる利用者の契約追加で404となるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     */
    public function failedWithNotFoundWhenUserIsOutsideOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when User is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);
        $user = $this->examples->users[14];
        $userId = $user->id;

        $I->sendPOST("users/{$userId}/ltcs-contracts", $this->domainToArray($this->examples->contracts[1]));

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
            "users/{$userId}/ltcs-contracts/",
            $this->domainToArray($this->examples->contracts[0])
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
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendPOST("users/{$this->examples->users[0]->id}/ltcs-contracts", $this->domainToArray($this->examples->contracts[0]));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
