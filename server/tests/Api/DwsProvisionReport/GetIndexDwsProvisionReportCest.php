<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\Contract\ContractStatus;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportDigest;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\Staff\Staff;
use Domain\User\User;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport getIndex のテスト
 * GET /dws-provision-reports
 */
class GetIndexDwsProvisionReportCest extends DwsProvisionReportTest
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
        $report = $this->examples->dwsProvisionReports[0];
        $officeId = $report->officeId;
        $providedIn = $report->providedIn;
        $status = $report->status;
        $expected = $this->expected($staff, $officeId, $providedIn, $status);

        $I->sendGET(
            'dws-provision-reports',
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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET('dws-provision-reports', [
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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET('dws-provision-reports', [
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
     * 受給者証の登録がない利用者が存在するテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedApiCallWhenCertificationNotExists(ApiTester $I)
    {
        $I->wantTo('succeed API call when Certification not exists');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $officeId = $this->examples->offices[3]->id;
        $providedIn = Carbon::create(2020, 2);
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET("dws-provision-reports?page=1&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
        $expected = $this->expectedPermittedOfficesOnly($staff, $officeId, $providedIn);

        $I->sendGET('dws-provision-reports', [
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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET("dws-provision-reports?sortBy=id&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->contracts[30]->contractedOn; // 契約日が月の途中
        $expected = $this->expected($staff, $officeId, $providedIn);

        $I->sendGET("dws-provision-reports?sortBy=id&officeId={$officeId}&providedIn={$providedIn->format('Y-m')}");

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
        $officeId = $this->examples->dwsProvisionReports[0]->officeId;
        $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;

        $I->sendGET('dws-provision-reports', [
            'officeId' => $officeId,
            'providedIn' => $providedIn->format('Y-m'),
        ]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * テストの期待値を返却する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @param null|\Domain\ProvisionReport\DwsProvisionReportStatus $status
     * @return array
     */
    private function expected(Staff $staff, int $officeId, Carbon $providedIn, ?DwsProvisionReportStatus $status = null): array
    {
        $userIds = $this->userIds($staff, $officeId, $providedIn)
            ->map(fn (Contract $x): int => $x->userId)
            ->distinct()
            ->toArray();
        $users = $this->users($staff, $userIds);
        $dwsCertifications = $this->dwsCertifications($userIds, $providedIn);
        $dwsProvisionReports = $this->dwsProvisionReports($userIds, $providedIn);

        $digests = $users->flatMap(function (User $user) use ($dwsCertifications, $dwsProvisionReports): Option {
            $option = $dwsCertifications->find(fn (dwsCertification $x): bool => $x->userId === $user->id);
            if ($option->isEmpty()) {
                return Option::none();
            }

            $dwsNumber = $option->map(fn (dwsCertification $x) => $x->dwsNumber)
                ->getOrElseValue('');
            $status = $dwsProvisionReports->find(fn (dwsProvisionReport $x): bool => $x->userId === $user->id)
                ->map(fn (dwsProvisionReport $x) => $x->status)
                ->getOrElseValue(DwsProvisionReportStatus::notCreated());
            return Option::from(DwsProvisionReportDigest::create([
                'userId' => $user->id,
                'name' => $user->name,
                'dwsNumber' => $dwsNumber,
                'isEnabled' => $user->isEnabled,
                'status' => $status,
            ]));
        });

        return (empty($status)
            ? $digests
            : $digests->filter(fn (DwsProvisionReportDigest $x): bool => $x->status === $status))
            ->map(fn (DwsProvisionReportDigest $x): array => Json::decode(Json::encode($x), true))
            ->toArray();
    }

    /**
     * 認可用期待値を作成する.
     *
     * @param \Domain\Staff\Staff $staff
     * @param int $officeId
     * @param \Domain\Common\Carbon $providedIn
     * @return array
     */
    private function expectedPermittedOfficesOnly(Staff $staff, int $officeId, Carbon $providedIn): array
    {
        $userIds = $this->userIds($staff, $officeId, $providedIn)
            ->filter(fn (Contract $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->map(fn (Contract $x): int => $x->userId)
            ->distinct()
            ->toArray();
        $users = $this->users($staff, $userIds);
        $DwsCertifications = $this->dwsCertifications($userIds, $providedIn);
        $DwsProvisionReports = $this->dwsProvisionReports($userIds, $providedIn)
            ->filter(fn (DwsProvisionReport $x): bool => in_array($x->officeId, $staff->officeIds, true));

        return $users->map(function (User $user) use ($DwsCertifications, $DwsProvisionReports) {
            $dwsNumber = $DwsCertifications->find(fn (DwsCertification $x): bool => $x->userId === $user->id)
                ->map(fn (DwsCertification $x) => $x->dwsNumber)
                ->getOrElseValue('');
            $status = $DwsProvisionReports->find(fn (DwsProvisionReport $x): bool => $x->userId === $user->id)
                ->map(fn (DwsProvisionReport $x) => $x->status)
                ->getOrElseValue(DwsProvisionReportStatus::notCreated());
            return DwsProvisionReportDigest::create([
                'userId' => $user->id,
                'name' => $user->name,
                'dwsNumber' => $dwsNumber,
                'isEnabled' => $user->isEnabled,
                'status' => $status,
            ]);
        })
            ->map(fn (DwsProvisionReportDigest $x): array => Json::decode(Json::encode($x), true))
            ->toArray();
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
            ->filter(fn (Contract $x): bool => $x->serviceSegment === ServiceSegment::disabilitiesWelfare());
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
     * 障害福祉サービス受給者証を取得する.
     *
     * @param array $userIds
     * @param Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Seq
     */
    private function dwsCertifications(array $userIds, Carbon $providedIn): Seq
    {
        return Seq::fromArray($this->examples->dwsCertifications)
            ->filter(fn (DwsCertification $x): bool => in_array($x->userId, $userIds, true))
            ->filter(fn (DwsCertification $x): bool => $x->effectivatedOn <= $providedIn)
            ->filter(fn (DwsCertification $x): bool => $x->status === DwsCertificationStatus::approved())
            ->sortBy(fn (DwsCertification $x): string => $x->effectivatedOn->toDateString())
            ->reverse()
            ->distinctBy(fn (DwsCertification $x): int => $x->userId);
    }

    /**
     * 障害福祉サービス：予実を取得する.
     *
     * @param array $userIds
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    private function dwsProvisionReports(array $userIds, Carbon $providedIn): Seq
    {
        return Seq::fromArray($this->examples->dwsProvisionReports)
            ->filter(fn (dwsProvisionReport $x): bool => in_array($x->userId, $userIds, true))
            ->filter(fn (dwsProvisionReport $x): bool => $x->providedIn->equalTo($providedIn));
    }
}
