<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Shift\Activity;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Lib\Json;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Shift update のテスト.
 * PUT /shifts
 */
class UpdateShiftCest extends ShiftTest
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
        $shift = $this->examples->shifts[4];
        $param = $this->buildParamFromExample($shift);

        $I->sendPUT("shifts/{$shift->id}", $param);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務シフトが更新されました', [
            'id' => $shift->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("shifts/{$shift->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected, $actual);
    }

    /**
     * 所要時間を更新できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWithDuration(ApiTester $I)
    {
        $I->wantTo('succeed API call with duration');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $example = $this->examples->shifts[4];
        $shift = $example->copy([
            'task' => Task::dwsVisitingCareForPwsd(),
            'durations' => [
                Duration::create([
                    'activity' => Activity::dwsVisitingCareForPwsd(),
                    'duration' => $example->schedule->end->diffInMinutes($example->schedule->start),
                ]),
            ],
        ]);

        $I->sendPUT("shifts/{$shift->id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務シフトが更新されました', [
            'id' => $shift->id,
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // 更新後のチェック
        $I->sendGET("shifts/{$shift->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        assert(count(JSON::decode($I->grabResponse(), true)['shift']['durations']) === 1); // 更新前のものは消えていることをassert
        $I->seeLogCount(0);
    }

    /**
     * ID存在しないテスト.
     *
     * @param ApiTester $I
     */
    public function failsWithNotFoundWhenIdNotExists(ApiTester $I)
    {
        $I->wantTo('fails with NOT FOUND when id not exists.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("shifts/{$id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Shift({$id}) not found");
    }

    /**
     * IDが事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4];
        $id = $this->examples->shifts[2]->id;

        $I->sendPUT("shifts/{$id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Shift({$id}) not found");
    }

    /**
     * Officeが事業者に存在していないと400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4]->copy(['officeId' => $this->examples->offices[1]->id]);
        $id = $shift->id;

        $I->sendPUT("shifts/{$id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * パラメータに指定された officeId が、アクセス認可されていない事業所の場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIsNotPermitted(ApiTester $I)
    {
        $I->wantTO('failed with BadRequest when Office is not permitted');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4]->copy(['officeId' => $this->examples->offices[2]->id]);
        $id = $shift->id;

        $I->sendPUT("shifts/{$id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 勤務区分に対応しないサービスオプションが指定された場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenServiceOptionIsInvalid(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when service option is invalid');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4]->copy([
            'task' => Task::ltcsPhysicalCareAndHousework()->value(),
            'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
        ]);

        $I->sendPUT("/shifts/{$shift->id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['options.0' => ['正しいサービスオプションを指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * IDがアクセス可能な事業所に存在していない場合に404を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithNotFoundWhenIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when ID is not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $shift = $this->examples->shifts[4];
        $id = $this->examples->shifts[2]->id;

        $I->sendPUT("shifts/{$id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Shift({$id}) not found");
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
        $shift = $this->examples->shifts[0];

        $I->sendPUT("shifts/{$shift->id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 開始日が過去の勤務シフトは編集できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenStartDateTimeIsPast(ApiTester $I)
    {
        $I->wantTo('fail when start date time is past');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $start = Carbon::now()->subHours(2);
        $end = $start->addHour();
        $duration = Duration::create([
            'activity' => $this->examples->shifts[11]->task->toActivitiesSeq()->head(),
            'duration' => $end->diffInMinutes($start),
        ]);
        $shift = $this->examples->shifts[11]->copy([
            'schedule' => Schedule::create([
                'start' => $start,
                'end' => $end,
                'date' => Carbon::now()->startOfDay(),
            ]),
            'durations' => [$duration],
        ]);

        $I->sendPUT("shifts/{$shift->id}", $this->buildParamFromExample($shift));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['id' => ['過去の勤務シフトは編集できません。']]]);
        $I->seeLogCount(0);
    }
}
