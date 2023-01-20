<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserLtcsCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\User\UserLtcsCalcSpec;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserLtcsCalcSpec Create のテスト.
 * POST /users/{userId}/ltcs-calc-specs
 */
class CreateUserLtcsCalcSpecCest extends UserLtcsCalcSpecTest
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
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];

        $I->sendPOST(
            "users/{$this->examples->users[0]->id}/ltcs-calc-specs",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：利用者別算定情報が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * アクセスできない利用者を指定すると404が返るテスト（認可）.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when userId is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $userId = $this->examples->users[1]->id;

        $I->sendPOST(
            "users/{$userId}/ltcs-calc-specs/",
            $this->defaultParam($this->examples->userLtcsCalcSpecs[0]),
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 別事業者の利用者 ID が指定された場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when UserID not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];
        $userId = $this->examples->users[14]->id;

        $I->sendPOST("users/{$userId}/ltcs-calc-specs", $this->defaultParam($userLtcsCalcSpec));

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

        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];

        $I->sendPOST(
            "users/{$this->examples->users[0]->id}/ltcs-calc-specs",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * テストパラメータの設定.
     *
     * @param \Domain\User\UserLtcsCalcSpec $userLtcsCalcSpec
     * @return array
     */
    private function defaultParam(UserLtcsCalcSpec $userLtcsCalcSpec): array
    {
        return $this->domainToArray($userLtcsCalcSpec);
    }
}
