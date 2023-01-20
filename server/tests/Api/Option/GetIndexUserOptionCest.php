<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Option;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Contract\Contract;
use Domain\Contract\ContractStatus;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Domain\User\User;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Option getIndex のテスト
 * GET /options/users
 */
class GetIndexUserOptionCest extends OptionTest
{
    use ExamplesConsumer;

    // tests

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
            ->map(fn (User $x): array => [
                'text' => $x->name->displayName,
                'value' => $x->id,
            ])
            ->toArray();

        $I->sendGET('/options/users', [
            'permission' => Permission::listUsers()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定した事業所の利用者のみ取得できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSpecifyOfficeIds(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify officeIds');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeIds = [$this->examples->offices[0]->id, $this->examples->offices[2]->id];
        $expected = $this->expectedFilterByOfficeIds($staff, $officeIds);

        $I->sendGET('/options/users', [
            'permission' => Permission::listUsers()->value(),
            'officeIds' => $officeIds,
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 認可された事業所の利用者のみ取得できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedApiCallWithOnlyUsersWhoBelongToTheAuthorizedOffice(ApiTester $I)
    {
        $I->wantTo('succeed API call with only users who belong to the authorized office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $expected = $this->expectedFilterByOfficeIds($staff, $staff->officeIds);

        $I->sendGET('/options/users', [
            'permission' => Permission::listUsers()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定された権限を持っていない場合、400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenSpecifyUnauthorizedPermission(ApiTester $I)
    {
        $I->wantTo('fail with bad request when specify unauthorized permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('/options/users', [
            'permission' => Permission::listUsers()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['permission' => ['権限を持っていません。']]]);
    }

    /**
     * @param \Domain\Staff\Staff $staff
     * @param array|int[] $officeIds
     * @return array
     */
    private function expectedFilterByOfficeIds(Staff $staff, array $officeIds): array
    {
        return Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(
                fn (User $x): bool => Seq::fromArray($this->examples->contracts)
                    ->exists(
                        fn (Contract $contract) => $contract->userId === $x->id
                            // 利用者が指定された事業所との契約の情報を持つ
                            && in_array($contract->officeId, $officeIds, true)
                            // 「契約状態が仮契約」または「契約状態が本契約かつ契約日が今日以前」
                            && (
                                $contract->status === ContractStatus::provisional()
                                && $contract->contractedOn <= Carbon::today()
                                || $contract->status === ContractStatus::formal()
                            )
                    )
            )
            ->sortBy(fn (User $x): string => $this->replace_to_seion($x->name->phoneticDisplayName))
            ->map(fn (User $x): array => [
                'text' => $x->name->displayName,
                'value' => $x->id,
            ])
            ->toArray();
    }
}
