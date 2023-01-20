<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractStatus;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportDigest;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\Staff\Staff;
use Domain\User\User;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProvisionReport getIndex のテスト.
 * GET /ltcs-provision-reports
 */
class GetIndexLtcsProvisionReportCest extends LtcsProvisionReportTest
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
        $report = $this->examples->ltcsProvisionReports[0];
        $officeId = $report->officeId;
        $providedIn = $report->providedIn;
        $status = $report->status;
        $expected = $this->expected($staff, $officeId, $providedIn, $status);

        $I->sendGET(
            'ltcs-provision-reports',
            [
                'officeId' => $officeId,
                'providedIn' => $providedIn->format('Y-m'),
                'status' => $status->value(),
            ]
        );
        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * ページ番号指定テスト
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithPageParam(ApiTester $I)
    {
        $I->wantTo('succeed API call with Page Param');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET('ltcs-provision-reports', [
            'page' => 2,
            'itemsPerPage' => 1,
            'officeId' => $officeId,
            'providedIn' => $providedIn->format('Y-m'),
        ]);

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 1, 1, 'name', ['page' => 2]);
    }

    /**
     * 実際にall=trueと指定して動作するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWhenSpecifyAll(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify all');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET('ltcs-provision-reports', [
            'all' => true,
            'officeId' => $officeId,
            'providedIn' => $providedIn->format('Y-m'),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson(
            $expected,
            0,
            count($expected),
            'name',
            ['itemsPerPage' => count($expected)]
        );
    }

    /**
     * 保険証の登録がない利用者が存在するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWhenInsCardNotExists(ApiTester $I)
    {
        $I->wantTo('succeed API call when InsCard not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $officeId = $this->examples->offices[3]->id;
        $providedIn = Carbon::create(2020, 2);
        $expected = $this->expected($staff->copy(['officeIds' => [4]]), $officeId, $providedIn);

        $I->sendGET("ltcs-provision-reports?page=1&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseContainsPaginationJson(
            $expected,
            0,
            count($expected),
            'name',
            ['itemsPerPage' => 10]
        );
    }

    /**
     * 認可された事業所だけ取得できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWithPermittedOfficesOnly(ApiTester $I)
    {
        $I->wantTo('succeed API call with permitted Offices only');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;
        $expected = $this->expectedPermittedOfficesOnly($staff, $officeId, $providedIn);

        $I->sendGET('ltcs-provision-reports', [
            'officeId' => $officeId,
            'providedIn' => $providedIn->format('Y-m'),
        ]);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'name');
    }

    /**
     * 日付のフィルタパラメータを指定して正しく動作するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenSpecifyFilterParamsOfDate(ApiTester $I)
    {
        $I->wantTo('succeed API call when specify filter params of date');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET("ltcs-provision-reports?sortBy=id&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 契約日が月の途中の利用者が、その月を指定した検索で取得されるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenContractedDateIsMiddleOfMonth(ApiTester $I)
    {
        $I->wantTo('succeed api call when contracted date is middle of month');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->contracts[30]->contractedOn; // 契約日が月の途中
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET("ltcs-provision-reports?sortBy=id&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithForbiddenWhenNoPermission(ApiTester $I)
    {
        $I->wantTo('failed with Forbidden when no permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $officeId = $this->examples->ltcsProvisionReports[0]->officeId;
        $providedIn = $this->examples->ltcsProvisionReports[0]->providedIn;

        $I->sendGET('ltcs-provision-reports', [
            'officeId' => $officeId,
            'providedIn' => $providedIn->format('Y-m'),
        ]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 期待値を生成する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @param null|\Domain\ProvisionReport\LtcsProvisionReportStatus $status
     * @return array&\Domain\ProvisionReport\LtcsProvisionReportDigest[]
     */
    private function expected(Staff $staff, int $officeId, Carbon $providedIn, ?LtcsProvisionReportStatus $status = null): array
    {
        $userIds = $this->userIds($staff, $officeId, $providedIn)
            ->filter(fn (Contract $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->map(fn (Contract $x): int => $x->userId)
            ->distinct()
            ->toArray();
        $users = $this->users($staff, $userIds);
        $ltcsInsCards = $this->ltcsInsCards($userIds, $providedIn);
        $ltcsProvisionReports = $this->ltcsProvisionReports($userIds, $providedIn);

        return $this->buildLtcsProvisionReportDigests($users, $ltcsInsCards, $ltcsProvisionReports, $status);
    }

    /**
     * 認可用期待値を生成する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @return array&\Domain\ProvisionReport\LtcsProvisionReportDigest[]
     */
    private function expectedPermittedOfficesOnly(Staff $staff, int $officeId, Carbon $providedIn): array
    {
        $userIds = $this->userIds($staff, $officeId, $providedIn)
            ->filter(fn (Contract $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->map(fn (Contract $x): int => $x->userId)
            ->distinct()
            ->toArray();
        $users = $this->users($staff, $userIds);
        $ltcsInsCards = $this->ltcsInsCards($userIds, $providedIn);
        $ltcsProvisionReports = $this->ltcsProvisionReports($userIds, $providedIn)
            ->filter(fn (LtcsProvisionReport $x): bool => in_array($x->officeId, $staff->officeIds, true));

        return $this->buildLtcsProvisionReportDigests($users, $ltcsInsCards, $ltcsProvisionReports);
    }

    /**
     * 契約から利用者IDを取得する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Contract\Contract[]&\ScalikePHP\Seq
     */
    private function userIds(Staff $staff, int $officeId, Carbon $providedIn): Seq
    {
        return Seq::fromArray($this->examples->contracts)
            ->filter(fn (Contract $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Contract $x): bool => $x->officeId === $officeId)
            ->filter(
                fn (Contract $x): bool => $x->status === ContractStatus::provisional()
                || (
                    $x->status === ContractStatus::formal()
                    && $x->contractedOn < $providedIn->endOfMonth()
                    && ($x->terminatedOn >= $providedIn->startOfMonth() || $x->terminatedOn === null)
                )
            )
            ->filter(fn (Contract $x): bool => $x->serviceSegment === ServiceSegment::longTermCare());
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param array $userIds
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    private function users(Staff $staff, array $userIds): Seq
    {
        return Seq::fromArray($this->examples->users)
            ->filter(fn (User $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (User $x): bool => in_array($x->id, $userIds, true))
            ->sortBy(fn (User $x): string => $x->name->phoneticDisplayName);
    }

    /**
     * 介護保険サービス被保険者証を取得する.
     *
     * @param array $userIds
     * @param Carbon $providedIn
     * @return \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Seq
     */
    private function ltcsInsCards(array $userIds, Carbon $providedIn): Seq
    {
        return Seq::fromArray($this->examples->ltcsInsCards)
            ->filter(fn (LtcsInsCard $x): bool => in_array($x->userId, $userIds, true))
            ->filter(fn (LtcsInsCard $x): bool => $x->effectivatedOn <= $providedIn)
            ->filter(fn (LtcsInsCard $x): bool => $x->status === LtcsInsCardStatus::approved())
            ->sortBy(fn (LtcsInsCard $x): string => $x->effectivatedOn->toDateString())
            ->reverse()
            ->distinctBy(fn (LtcsInsCard $x): int => $x->userId);
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param array $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq
     */
    private function ltcsProvisionReports(array $userIds, Carbon $providedIn): Seq
    {
        return Seq::fromArray($this->examples->ltcsProvisionReports)
            ->filter(fn (LtcsProvisionReport $x): bool => in_array($x->userId, $userIds, true))
            ->filter(fn (LtcsProvisionReport $x): bool => $x->providedIn->equalTo($providedIn));
    }

    /**
     * 予実概要を生成する.
     *
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Seq $ltcsInsCards
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]&\ScalikePHP\Seq $ltcsProvisionReports
     * @param null|\Domain\ProvisionReport\LtcsProvisionReportStatus $status
     * @return array&\Domain\ProvisionReport\LtcsProvisionReportDigest[]
     */
    private function buildLtcsProvisionReportDigests(
        Seq $users,
        Seq $ltcsInsCards,
        Seq $ltcsProvisionReports,
        ?LtcsProvisionReportStatus $status = null
    ): array {
        $digests = $users->flatMap(function (User $user) use ($ltcsInsCards, $ltcsProvisionReports): Option {
            $option = $ltcsInsCards->find(fn (LtcsInsCard $x): bool => $x->userId === $user->id);
            if ($option->isEmpty()) {
                return Option::none();
            }
            $insNumber = $option
                ->map(fn (LtcsInsCard $x) => $x->insNumber)
                ->getOrElseValue('');
            $status = $ltcsProvisionReports->find(fn (LtcsProvisionReport $x): bool => $x->userId === $user->id)
                ->map(fn (LtcsProvisionReport $x) => $x->status)
                ->getOrElseValue(LtcsProvisionReportStatus::notCreated());
            return Option::from(LtcsProvisionReportDigest::create([
                'userId' => $user->id,
                'name' => $user->name,
                'insNumber' => $insNumber,
                'isEnabled' => $user->isEnabled,
                'status' => $status,
            ]));
        });

        return (empty($status)
            ? $digests
            : $digests->filter(fn (LtcsProvisionReportDigest $x): bool => $x->status === $status))
            ->map(fn (LtcsProvisionReportDigest $x): array => Json::decode(Json::encode($x), true))
            ->toArray();
    }
}
