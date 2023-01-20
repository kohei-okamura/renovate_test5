<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\User;

use ApiTester;
use Codeception\Util\HttpCode;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Subsidies update のテスト.
 * PUT /users/{userId}/ltcs-subsidies/{id}
 */
class UpdateUserLtcsSubsidyCest extends UserLtcsSubsidyTest
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
        $I->wantTo('succeed API Call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $param = $this->domainToArray($userLtcsSubsidy->copy(['benefitRate' => 80]));

        $I->sendPUT("/users/{$this->examples->users[0]->id}/ltcs-subsidies/{$userLtcsSubsidy->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '公費情報が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $userLtcsSubsidy->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("/users/{$this->examples->users[0]->id}/ltcs-subsidies/{$userLtcsSubsidy->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
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
        $id = self::NOT_EXISTING_ID;
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $param = $this->domainToArray($userLtcsSubsidy->copy(['benefitRate' => 80]));

        $I->sendPUT("/users/{$this->examples->users[0]->id}/ltcs-subsidies/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "UserLtcsSubsidy({$id}) not found"
        );
    }

    /**
     * 利用者が存在しないと404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserDoesNotExist(ApiTester $I)
    {
        $I->wantTo('failed with not found when user does not exist');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $userId = self::NOT_EXISTING_ID;
        $param = $this->domainToArray($userLtcsSubsidy->copy(['benefitRate' => 80]));

        $I->sendPUT("/users/{$userId}/ltcs-subsidies/{$userLtcsSubsidy->id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "User[{$userId}] is not found"
        );
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
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $userId = $this->examples->users[14]->id;
        $param = $this->domainToArray($userLtcsSubsidy->copy(['benefitRate' => 80]));

        $I->sendPUT("/users/{$userId}/ltcs-subsidies/{$userLtcsSubsidy->id}", $param);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "User[{$userId}] is not found"
        );
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
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $id = $userLtcsSubsidy->id;

        $I->sendPUT("users/{$userId}/ltcs-subsidies/{$id}", $this->domainToArray($userLtcsSubsidy));

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

        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $param = $this->domainToArray($userLtcsSubsidy->copy(['benefitRate' => 80]));

        $I->sendPUT("/users/{$this->examples->users[0]->id}/ltcs-subsidies/{$userLtcsSubsidy->id}", $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}
