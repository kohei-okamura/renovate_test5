<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\UserDwsCalcSpec;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * UserDwsCalcSpec update のテスト.
 * PUT /users/{userId}/dws-calc-specs/{id}
 */
class UpdateUserDwsCalcSpecCest extends UserDwsCalcSpecTest
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
        $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => DwsUserLocationAddition::specifiedArea(),
        ]);
        $I->sendPut(
            "/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$userDwsCalcSpec->id}",
            $this->defaultParam($userDwsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：利用者別算定情報が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $userDwsCalcSpec->id,
        ]);

        $returned = $I->grabResponseArray();

        $I->sendGet("/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$userDwsCalcSpec->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();

        $expected = [
            'dwsCalcSpec' => $this->domainToArray($userDwsCalcSpec->copy(['updatedAt' => Carbon::now()])),
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
        $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => DwsUserLocationAddition::specifiedArea(),
        ]);
        $I->sendPut(
            "/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$id}",
            $this->defaultParam($userDwsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(
            0,
            LogLevel::WARNING,
            "UserDwsCalcSpec({$id}) not found"
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
        $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[3]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => DwsUserLocationAddition::specifiedArea(),
        ]);
        $I->sendPut(
            "/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$userDwsCalcSpec->id}",
            $this->defaultParam($userDwsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userDwsCalcSpec->userId}] is not found");
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
        $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[2]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => DwsUserLocationAddition::specifiedArea(),
        ]);
        $I->sendPut(
            "/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$userDwsCalcSpec->id}",
            $this->defaultParam($userDwsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userDwsCalcSpec->userId}] is not found");
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
        $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[0]->copy([
            'effectivatedOn' => Carbon::parse('2022-04-01'),
            'locationAddition' => DwsUserLocationAddition::specifiedArea(),
        ]);
        $I->sendPut(
            "/users/{$userDwsCalcSpec->userId}/dws-calc-specs/{$userDwsCalcSpec->id}",
            $this->defaultParam($userDwsCalcSpec)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 入力値の組み立て.
     *
     * @param UserDwsCalcSpec $userDwsCalcSpec
     * @return array
     */
    private function defaultParam(UserDwsCalcSpec $userDwsCalcSpec): array
    {
        return [
            'effectivatedOn' => $userDwsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $userDwsCalcSpec->locationAddition->value(),
        ];
    }
}
