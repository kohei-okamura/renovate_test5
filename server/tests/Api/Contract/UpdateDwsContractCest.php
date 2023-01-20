<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Contract;

use ApiTester;
use Closure;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Illuminate\Support\Arr;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsContract update のテスト
 * PUT /users/{userId}/dws-contracts/{id}
 */
final class UpdateDwsContractCest extends ContractTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * 正常呼び出しテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICall(ApiTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $contract = $this->getContract();

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $contract->id,
        ]);
        $I->seeResponseContainsJson($this->domainToArray([
            'contract' => $contract->copy(['updatedAt' => Carbon::now()]),
        ]));
    }

    /**
     * 本契約に更新するテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICallWithStatusIsFormal(ApiTester $I): void
    {
        $I->wantTo('succeed API call with Status is formal');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $contract = $this->getContract([
            'status' => ContractStatus::formal(),
        ]);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract, function (array &$params): void {
                Arr::forget($params, ['terminatedOn']);
            })
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $contract->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("users/{$contract->userId}/dws-contracts/{$contract->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        assertSame($expected, $actual);
    }

    /**
     * 契約を無効にするテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICallWithStatusIsDisabled(ApiTester $I): void
    {
        $I->wantTo('succeed API call with status is disabled');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $contract = $this->getContract();

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            ['status' => ContractStatus::disabled()->value()]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $contract->id,
        ]);
        $expected = $I->grabResponseArray();

        $I->sendGET("users/{$contract->userId}/dws-contracts/{$contract->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $actual = $I->grabResponseArray();
        assertSame($expected, $actual);
        assertSame(Carbon::now()->format(Carbon::ISO8601), $actual['contract']['updatedAt']);
    }

    /**
     * 契約が重複していない場合に更新できるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICallWhenContractIsNotOverlapped(ApiTester $I): void
    {
        $I->wantTo('succeed API call when contract is not overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $carbon = Carbon::parse('2021-03-02');
        $params = [
            'contractedOn' => $carbon,
            'terminatedOn' => $carbon->addMonth(),
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => $carbon,
                    'end' => $carbon->addMonth(),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => $carbon,
                    'end' => $carbon->addMonth(),
                ]),
            ],
            'ltcsPeriod' => ContractPeriod::create([]),
            'expiredReason' => LtcsExpiredReason::unspecified(),
        ];
        $contract = $this->examples->contracts[29]->copy($params);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '契約が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $contract->id,
        ]);
        $I->seeResponseContainsJson($this->domainToArray([
            'contract' => $contract->copy(['updatedAt' => Carbon::now()]),
        ]));
    }

    /**
     * ID に対応する契約が存在しないので 404 を返すテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I): void
    {
        $I->wantTo('failed with NotFound when Invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $contract = $this->getContract();
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Contract({$id}) not found");
    }

    /**
     * 異なる事業者の事業所 ID の場合 400 が返るテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithBadRequestWhenOfficeIdIsOutsideOrganization(ApiTester $I): void
    {
        $I->wantTo('failed with BAD REQUEST when officeId is outside organization.');

        // 管理者を用いる
        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);

        // 異なる事業者の事業所 ID を用いる
        $contract = $this->getContract([
            'officeId' => $this->examples->offices[1]->id,
        ]);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能でない事業所の場合 400 が返るテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithBadRequestWhenOfficeIdIsNotAccessible(ApiTester $I): void
    {
        $I->wantTo('failed with Bad Request when OfficeId is not accessible');

        // 事業所管理者を用いる
        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        // 事業者は同じだが $staff が権限を持たない事業所の ID を用いる
        $contract = $this->getContract([
            'officeId' => $this->examples->offices[2]->id,
        ]);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 利用者が存在しない場合に 404 を返すテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithNotFoundWhenInvalidUserId(ApiTester $I): void
    {
        $I->wantTo('failed with NotFound when Invalid UserID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $contract = $this->getContract();
        $userId = self::NOT_EXISTING_ID;

        $I->sendPUT(
            "users/{$userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 事業者が異なる利用者で 404 となるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithNotFoundWhenUserIsOutsideOrganization(ApiTester $I): void
    {
        $I->wantTo('failed with NotFound when User is outside Organization');

        // 管理者を用いる
        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);

        $contract = $this->getContract();
        $user = $this->examples->users[14];

        $I->sendPUT(
            "users/{$user->id}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$user->id}] is not found");
    }

    /**
     * 事業者が異なる契約 ID で404となるテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithNotFoundWhenIdIsOutsideOrganization(ApiTester $I): void
    {
        $I->wantTo('failed with NotFound when ID is outside Organization');

        $staff = $this->examples->staffs[30];
        $I->actingAs($staff);

        $contract = $this->examples->contracts[13];
        assert($staff->organizationId !== $contract->organizationId);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams()
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Contract({$contract->id}) not found");
    }

    /**
     * アクセス可能な事業所と契約がない利用者を指定すると404が返るテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failedWithNotFoundWhenUserIdIsNotInAccessibleOffice(ApiTester $I): void
    {
        $I->wantTo('failed with NotFound when UserID is not in accessible Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $target = $this->examples->contracts[15];
        $contract = $this->getContract([
            'officeId' => Seq::from(...$staff->officeIds)->head(),
        ]);

        $I->sendPUT(
            "users/{$target->userId}/dws-contracts/{$target->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$target->userId}] is not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I): void
    {
        $I->wantTo('fail with Forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $contract = $this->getContract();

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 契約が重複する場合に400が返るテスト.
     *
     * @param ApiTester $I
     * @throws \JsonException
     * @return void
     * @noinspection PhpUnused
     */
    public function failWithBadRequestWhenContractIsOverlapped(ApiTester $I): void
    {
        $I->wantTo('fail with bad request when contract is overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $carbon = Carbon::parse('2021-01-31');
        $contract = $this->examples->contracts[23]->copy([
            'contractedOn' => $carbon,
            'terminatedOn' => $carbon->addMonth(),
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => $carbon,
                    'end' => $carbon->addMonth(),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => $carbon,
                    'end' => $carbon->addMonth(),
                ]),
            ],
        ]);

        $I->sendPUT(
            "users/{$contract->userId}/dws-contracts/{$contract->id}",
            $this->createParams($contract)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['contractedOn' => ['重複する契約が既に登録されています。ご確認ください。']]]);
    }

    /**
     * テスト用の契約を生成する.
     *
     * @param array $attrs
     * @return \Domain\Contract\Contract
     */
    private function getContract(array $attrs = []): Contract
    {
        $values = [
            'officeId' => $this->examples->offices[2]->id,
            'contractedOn' => Carbon::create(2020, 1, 1),
            'terminatedOn' => Carbon::create(2020, 12, 31),
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2020, 1, 5),
                    'end' => Carbon::create(2020, 5, 31),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2020, 6, 1),
                    'end' => Carbon::create(2020, 12, 25),
                ]),
            ],
            'ltcsPeriod' => ContractPeriod::create([]),
            'expiredReason' => LtcsExpiredReason::unspecified(),
            'note' => 'だるまさんが転んだ',
        ];
        return $this->examples->contracts[0]->copy($attrs + $values);
    }

    /**
     * リクエスト用パラメータを生成する.
     *
     * @param null|\Domain\Contract\Contract $contract
     * @param null|\Closure $f
     * @throws \JsonException
     * @return array
     */
    private function createParams(?Contract $contract = null, ?Closure $f = null): array
    {
        $params = ['isEnabled' => true] + $this->domainToArray($contract ?? $this->getContract());
        if ($f !== null) {
            $f($params);
        }
        return $params;
    }
}
