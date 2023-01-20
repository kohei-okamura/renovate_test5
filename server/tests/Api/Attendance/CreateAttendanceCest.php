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
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Attendance create のテスト
 * POST /attendances
 */
class CreateAttendanceCest extends AttendanceTest
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

        $I->sendPOST('attendances', $this->buildParamFromExample($this->examples->attendances[4]));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 仮契約で勤務実績登録できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithContractIsProvisional(ApiTester $I)
    {
        $I->wantTo('succeed API call with Contract is provisional.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('attendances', $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'task' => Task::ltcsHousework(),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::ltcsHousework(),
                        'duration' => $this->examples->attendances[4]->schedule->end->diffInMinutes($this->examples->attendances[4]->schedule->start),
                    ]),
                ],
                'userId' => $this->examples->users[4]->id, // 介護保険サービスは仮契約しかない
            ])
        ));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 担当者未定のときに登録できるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICallWithAssigneeIsUndecided(ApiTester $I)
    {
        $I->wantTo('succeed API call with assignee is undecided.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('attendances', $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'assignees' => [
                    ['isUndecided' => true],
                ],
            ])
        ));

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '勤務実績が登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 利用者が事業者外だった場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenUserIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when user_id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'userId' => $this->examples->users[14]->id,
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 利用者に契約がない場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenUserDoesNotHaveAnyContract(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when User does not have any contract');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'userId' => $this->examples->users[5]->id, // 契約のないユーザ
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['事業所に所属している利用者を指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 利用者の契約が終了している場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithBadRequestWhenContractsOfUserIsTerminated(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when Contracts of User is terminated');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'userId' => $this->examples->users[3]->id, // 契約が終了しているユーザ
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['事業所に所属している利用者を指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 事業所が事業者外だった場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when office_id is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'officeId' => $this->examples->offices[1]->id,
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
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
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'userId' => $this->examples->users[3]->id,
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['事業所に所属している利用者を指定してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 事業所のアクセスが認可されていない場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsNotPermitted(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when office_id is not permitted');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'officeId' => $this->examples->offices[2]->id,
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
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

        $I->sendPOST('attendances', $this->buildParamFromExample($this->examples->attendances[0]));

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
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'schedule' => Schedule::create([
                    'date' => Carbon::tomorrow(),
                    'start' => $this->examples->attendances[4]->schedule->start,
                    'end' => $this->examples->attendances[4]->schedule->end,
                ]),
            ])
        );

        $I->sendPOST('/attendances', $param);

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
        $param = $this->buildParamFromExample(
            $this->examples->attendances[4]->copy([
                'task' => Task::ltcsPhysicalCareAndHousework()->value(),
                'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
            ])
        );

        $I->sendPOST('/attendances', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['options.0' => ['正しいサービスオプションを指定してください。']]]);
        $I->seeLogCount(0);
    }
}
