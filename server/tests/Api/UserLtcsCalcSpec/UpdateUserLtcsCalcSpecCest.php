<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserLtcsCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserLtcsCalcSpec update のテスト.
 * PUT /users/{userId}/ltcs-calc-specs/{id}
 */
class UpdateUserLtcsCalcSpecCest extends UserLtcsCalcSpecTest
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
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
        ]);
        $I->sendPut(
            "/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$userLtcsCalcSpec->id}",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：利用者別算定情報が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $userLtcsCalcSpec->id,
        ]);

        $returned = $I->grabResponseArray();

        $I->sendGet("/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$userLtcsCalcSpec->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();

        $expected = [
            'ltcsCalcSpec' => $this->domainToArray($userLtcsCalcSpec->copy(['updatedAt' => Carbon::now()])),
        ];
        $I->assertSame($expected, $returned);
        $I->assertSame($expected, $stored);
    }

    /**
     * 存在しないIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundIfIdDoesNotExist(ApiTester $I)
    {
        $I->wantTo('fail with NotFound if id does not exist.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = self::NOT_EXISTING_ID;
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
        ]);
        $I->sendPut(
            "/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$id}",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "UserLtcsCalcSpec({$id}) not found"
        );
    }

    /**
     * 別の事業者の利用者 ID が指定された場合に404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenUserIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when user id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[3]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
        ]);
        $I->sendPut(
            "/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$userLtcsCalcSpec->id}",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userLtcsCalcSpec->userId}] is not found");
    }

    /**
     * アクセス可能でない利用者 ID が指定された場合に404が返るテスト（認可）.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenUserIdIsNotAccessible(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when user id is not accessible');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[2]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
        ]);
        $I->sendPut(
            "/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$userLtcsCalcSpec->id}",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userLtcsCalcSpec->userId}] is not found");
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
        $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
        ]);
        $I->sendPut(
            "/users/{$userLtcsCalcSpec->userId}/ltcs-calc-specs/{$userLtcsCalcSpec->id}",
            $this->defaultParam($userLtcsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param UserLtcsCalcSpec $userLtcsCalcSpec
     * @return array
     */
    private function defaultParam(UserLtcsCalcSpec $userLtcsCalcSpec): array
    {
        return [
            'effectivatedOn' => $userLtcsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $userLtcsCalcSpec->locationAddition->value(),
        ];
    }
}
