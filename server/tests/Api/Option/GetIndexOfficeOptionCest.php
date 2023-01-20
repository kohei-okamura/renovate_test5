<?php
/**
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
use Domain\Office\Office;
use Domain\Office\OfficeOption;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Option getIndex のテスト
 * GET /options/offices
 */
final class GetIndexOfficeOptionCest extends OptionTest
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     */
    public function succeedAPICall(ApiTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 事業者区分でフィルタするテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     */
    public function succeedAPICallFilteringByPurpose(ApiTester $I): void
    {
        $I->wantTo('succeed API call filtering by purpose');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $purpose = Purpose::external();
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (Office $x): bool => $x->purpose === $purpose)
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
            'purpose' => $purpose->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定区分でフィルタするテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     */
    public function succeedAPICallFilteringByQualification(ApiTester $I): void
    {
        $I->wantTo('succeed API call filtering by qualification');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $qualifications = [
            OfficeQualification::dwsHomeHelpService(),
            OfficeQualification::dwsVisitingCareForPwsd(),
        ];
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(
                fn (Office $office): bool => Seq::fromArray($qualifications)
                    ->exists(fn (OfficeQualification $x): bool => in_array($x, $office->qualifications, true))
            )
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
            'qualifications' => Seq::fromArray($qualifications)
                ->map(fn (OfficeQualification $x): string => $x->value())
                ->toArray(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 利用者IDに空文字が渡された場合に正常に処理されるテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICallWhenEmptyStringInUserIdGiven(ApiTester $I): void
    {
        $I->wantTo('succeed API call when empty string in user id given');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
            'userId' => '',
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 指定した利用者の所属する事業所のみ取得できるテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedAPICallWhenSpecifyUserId(ApiTester $I): void
    {
        $I->wantTo('succeed API call when specify userId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = $this->examples->users[0]->id;
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(
                fn (Office $x): bool => Seq::fromArray($this->examples->contracts)
                    ->exists(
                        fn (Contract $contract) => $contract->officeId === $x->id
                            && $contract->userId === $userId
                            // 「契約状態が仮契約」または「契約状態が本契約かつ契約日が今日以前」
                            && (
                                $contract->status === ContractStatus::formal()
                                && $contract->contractedOn <= Carbon::today()
                                || $contract->status === ContractStatus::provisional()
                            )
                    )
            )
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
            'userId' => $userId,
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseJson($expected);
    }

    /**
     * 認可された事業所のスタッフのみ取得できるテスト.
     *
     * @param ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     * @return void
     * @noinspection PhpUnused
     */
    public function succeedApiCallWithOnlyStaffsWhoBelongToTheAuthorizedOffice(ApiTester $I): void
    {
        $I->wantTo('succeed API call with only offices who belong to the authorized office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->offices)
            ->filter(fn (Office $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (Office $x): bool => in_array($x->id, $staff->officeIds, true))
            ->sortBy(fn (Office $x): string => $this->replace_to_seion($x->name))
            ->map(fn (Office $x): array => OfficeOption::from($x)->toAssoc())
            ->toArray();

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
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
     * @return void
     * @noinspection PhpUnused
     */
    public function failWithBadRequestWhenSpecifyUnauthorizedPermission(ApiTester $I): void
    {
        $I->wantTo('fail with bad request when specify unauthorized permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);

        $I->sendGET('/options/offices', [
            'permission' => Permission::listInternalOffices()->value(),
        ]);
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['permission' => ['権限を持っていません。']]]);
    }
}
