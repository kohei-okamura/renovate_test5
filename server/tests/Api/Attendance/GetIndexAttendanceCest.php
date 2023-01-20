<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Shift\Attendance;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Attendance getIndex のテスト.
 * GET /attendances
 */
class GetIndexAttendanceCest extends AttendanceTest
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
        $expected = Seq::fromArray($this->examples->attendances)
            ->filter(fn (Attendance $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Attendance $x): int => $x->id)
            ->map(fn (Attendance $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('attendances');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }

    /**
     * ソート指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortByUserId(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by userId');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->attendances)
            ->filter(fn (Attendance $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->sortBy(fn (Attendance $x): int => $x->userId)
            ->map(fn (Attendance $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('attendances', ['sortBy' => 'userId']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'userId');
        $I->seeLogCount(0);
    }

    /**
     * isConfirmed を false にして正しく動作するテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithIsConfirmedFalseFilter(ApiTester $I)
    {
        $I->wantTo('succeed API call with isConfirmed false filter');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->attendances)
            ->filter(fn (Attendance $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (Attendance $x): bool => !$x->isConfirmed)
            ->sortBy(fn (Attendance $x): int => $x->id)
            ->map(fn (Attendance $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('attendances?sortBy=id&isConfirmed=false');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
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
        $start = $this->examples->attendances[0]->schedule->date->subDay();
        $end = $this->examples->attendances[0]->schedule->date->addDay();
        $expected = Seq::fromArray($this->examples->attendances)
            ->filter(fn (Attendance $x): bool => $x->organizationId === $this->examples->staffs[0]->organizationId)
            ->filter(fn (Attendance $x): bool => $x->schedule->date->gte($start))
            ->filter(fn (Attendance $x): bool => $x->schedule->date->lte($end))
            ->sortBy(fn (Attendance $x): int => $x->id)
            ->map(fn (Attendance $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET("attendances?sortBy=id&start={$start->toDateString()}&end={$end->toDateString()}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
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

        $I->sendGET('attendances');

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * アクセスできる事業所に属しているエンティティのみ取得されるテスト.
     *
     * @param ApiTester $I
     */
    public function getEntitiesFromOnlyAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('get entities from only accessible offices');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $expected = Seq::fromArray($this->examples->attendances)
            ->filter(fn (Attendance $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Attendance $x): bool => in_array($x->officeId, $staff->officeIds, true))
            ->sortBy(fn (Attendance $x): int => $x->id)
            ->map(fn (Attendance $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('attendances');

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
    }
}
