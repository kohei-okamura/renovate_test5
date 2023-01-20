<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Shift\Activity;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Lib\Arrays;
use Lib\Json;
use function PHPUnit\Framework\assertSame;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Attendance update のテスト.
 * PUT /attendances/{id}
 */
class UpdateAttendanceCest extends AttendanceTest
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
        $attendance = $this->examples->attendances[4];

        $I->sendPUT("attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が更新されました', [
            'id' => "{$attendance->id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("attendances/{$attendance->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected, $actual);
    }

    /**
     * 担当者未定のときに更新できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWithAssigneeIsUndecided(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy([
            'assignees' => [
                [
                    'isUndecided' => true,
                ],
            ],
        ]);

        $I->sendPUT("attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が更新されました', [
            'id' => "{$attendance->id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // 更新後のチェック
        $keys = [
            'officeId',
            'userId',
            'assignerId',
            'task',
            'serviceCode',
            'headcount',
            'assignees',
            'schedule',
            'durations',
            'options',
            'note',
            'isConfirmed',
            'createdAt',
            'updatedAt',
        ];
        $expected = $attendance->copy(['updatedAt' => Carbon::now()]);
        $expectedArray = [
            'attendance' => Arrays::generate(function () use ($keys, $expected): iterable {
                foreach ($this->domainToArray($expected) as $key => $value) {
                    if (in_array($key, $keys, true)) {
                        yield $key => $value;
                    }
                }
            }),
        ];
        $I->sendGET("attendances/{$attendance->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson($expectedArray);
        $I->seeLogCount(0);
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
        $example = $this->examples->attendances[4];
        $attendance = $example->copy([
            'task' => Task::dwsVisitingCareForPwsd(),
            'durations' => [
                Duration::create([
                    'activity' => Activity::dwsVisitingCareForPwsd(),
                    'duration' => $example->schedule->end->diffInMinutes($example->schedule->start),
                ]),
            ],
        ]);

        $I->sendPUT("attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が更新されました', [
            'id' => "{$attendance->id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        // 更新後のチェック
        $I->sendGET("attendances/{$attendance->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        assert(count(JSON::decode($I->grabResponse(), true)['attendance']['durations']) === 1); // 更新前のものは消えていることをassert
        $I->seeLogCount(0);
    }

    /**
     * 仮契約で勤務実績更新できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithContractIsProvisional(ApiTester $I)
    {
        $I->wantTo('succeed API call with Contract is provisional.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy([
            'task' => Task::ltcsHousework(),
            'durations' => [
                Duration::create([
                    'activity' => Activity::ltcsHousework(),
                    'duration' => $this->examples->attendances[4]->schedule->end->diffInMinutes($this->examples->attendances[4]->schedule->start),
                ]),
            ],
            'userId' => $this->examples->users[4]->id, // 介護保険サービスは仮契約しかない
        ]);

        $I->sendPUT("attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が更新されました', [
            'id' => "{$attendance->id}",
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $actual = $I->grabResponseArray();

        $I->sendGET("attendances/{$attendance->id}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();

        assertSame($expected, $actual);
    }

    /**
     * 無効なIDを指定すると404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when Invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4];
        $id = self::NOT_EXISTING_ID;

        $I->sendPUT("attendances/{$id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
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
        $attendance = $this->examples->attendances[4];
        $id = $this->examples->attendances[2]->id;

        $I->sendPUT("attendances/{$id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
    }

    /**
     * Officeが事業者に存在していないと400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenOfficeIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy(['officeId' => $this->examples->offices[1]->id]);
        $id = $attendance->id;

        $I->sendPUT("attendances/{$id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * Userが事業者に存在していないと400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenUserIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy(['userId' => $this->examples->users[14]->id]);
        $id = $attendance->id;

        $I->sendPUT("attendances/{$id}", $this->buildParamFromExample($attendance));

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['正しい値を入力してください。']]]);
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
        $attendance = $this->examples->attendances[4];
        $id = $this->examples->attendances[2]->id;

        $I->sendPUT("attendances/{$id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Attendance({$id}) not found");
    }

    /**
     * 利用者が、アクセス可能な事業所にいない場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenUserIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when user_id is not in permitted office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy([
            'userId' => $this->examples->users[3]->id,
        ]);
        $id = $attendance->id;
        $param = $this->buildParamFromExample($attendance);

        $I->sendPUT("/attendances/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['事業所に所属している利用者を指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 事業所がアクセス可能でない場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsNotPermit(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when office_id is not permit');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy([
            'officeId' => $this->examples->offices[1]->id,
        ]);
        $id = $attendance->id;
        $param = $this->buildParamFromExample($attendance);

        $I->sendPUT("/attendances/{$id}", $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'userId' => ['事業所に所属している利用者を指定してください。'],
                'officeId' => ['正しい値を入力してください。'],
                'assignerId' => ['事業所に所属しているスタッフを指定してください。'],
                'assignees.0.staffId' => ['事業所に所属しているスタッフを指定してください。'],
            ],
        ]);
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
        $attendance = $this->examples->attendances[0];
        $param = $this->buildParamFromExample($attendance);

        $I->sendPUT("/attendances/{$attendance->id}", $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 未来日を指定した場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenFutureDateGiven(ApiTester $I)
    {
        $I->wantTo('failed with bad request when future date given');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $attendance = $this->examples->attendances[4]->copy([
            'schedule' => Schedule::create([
                'date' => Carbon::tomorrow(),
                'start' => $this->examples->attendances[4]->schedule->start,
                'end' => $this->examples->attendances[4]->schedule->end,
            ]),
        ]);

        $I->sendPUT("/attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['schedule.date' => [Carbon::tomorrow()->toDateString() . 'より前の日付を入力してください。']]]);
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
        $attendance = $this->examples->attendances[4]->copy([
            'task' => Task::ltcsPhysicalCareAndHousework()->value(),
            'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
        ]);

        $I->sendPUT("/attendances/{$attendance->id}", $this->buildParamFromExample($attendance));

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['options.0' => ['正しいサービスオプションを指定してください。']]]);
        $I->seeLogCount(0);
    }
}
