<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\User;

use ApiTester;
use Closure;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Contract\Contract;
use Domain\Contract\ContractStatus;
use Domain\User\User;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * User getIndex のテスト.
 * GET /users
 */
class GetUserIndexCest extends UserTest
{
    use ExamplesConsumer;

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
        $expected = Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (User $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (User $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('users');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * ソート指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortById(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by id');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (User $x): int => $x->id)
            ->map(fn (User $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('users', ['sortBy' => 'id']);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * officeId でフィルタするテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithOfficeId(ApiTester $I)
    {
        $I->wantTo('succeed API Call with officeId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->offices[0]->id;

        $expected = Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter($this->officeIdFilter($officeId))
            ->sortBy(fn (User $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (User $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("users?officeId={$officeId}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * isEnabled でフィルタするテスト.
     *
     * @param ApiTester $I
     */
    public function successAPICallWithIsEnabled(ApiTester $I)
    {
        $I->wantTo('success API call with isEnabled');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (User $x): bool => $x->isEnabled)
            ->sortBy(fn (User $x): int => $x->id)
            ->map(fn (User $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('users?isEnabled=1&sortBy=id');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * 認可された事業所だけ取得できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithOnlyUsersWhoBelongToTheAuthorizedOffice(ApiTester $I)
    {
        $I->wantTo('succeed api call with only users who belong to the authorized office');

        $staff = $this->examples->staffs[28]; // 事業所管理者
        $I->actingAs($staff);

        $expected = Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $staff->organizationId)
            ->filter($this->officeIdFilter(...$staff->officeIds))
            ->sortBy(fn (User $x): int => $x->id)
            ->map(fn (User $x): array => Json::decode(Json::encode($x), true))
            ->toArray();

        $I->sendGET('/users', ['sortBy' => 'id', 'itemsPerPage' => 10]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
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

        $I->sendGET('users');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 指定された officeId でフィルタするための関数を返す.
     *
     * @param int ...$officeIds
     * @return \Closure
     */
    private function officeIdFilter(int ...$officeIds): Closure
    {
        return fn (User $x): bool => Seq::fromArray($this->examples->contracts)
            ->exists(
                fn (Contract $contract) => $contract->userId === $x->id
                    // 利用者が指定された事業所との契約の情報を持つ
                    && in_array($contract->officeId, $officeIds, true)
                    // 「契約状態が仮契約」または「契約状態が本契約かつ契約日が今日以前」
                    && (
                        $contract->status === ContractStatus::provisional()
                        || $contract->status === ContractStatus::formal() && $contract->contractedOn <= Carbon::today()
                    )
            );
    }
}
