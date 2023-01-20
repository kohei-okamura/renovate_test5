<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateAttendanceRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Attendance;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CreateAttendanceRequest のテスト
 *
 * @property-read int $task
 * @property-read string $serviceCode
 * @property-read int $userId
 * @property-read int $officeId
 * @property-read int $contractId
 * @property-read int $assignerId
 * @property-read array $assignees
 * @property-read int $headcount
 * @property-read string $start
 * @property-read string $end
 * @property-read string $date
 * @property-read array $durations
 * @property-read array $options
 * @property-read string $note
 */
class CreateAttendanceRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CreateAttendanceRequest $request;
    private Attendance $attendance;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateAttendanceRequestTest $self): void {
            $self->request = new CreateAttendanceRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->attendance = $self->examples->attendances[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createAttendances(),
                    Mockery::any(),
                    $self->examples->users[1]->id,
                    anInstanceOf(ServiceSegment::class),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::none());

            $self->lookupContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createAttendances(),
                    $self->attendance->userId,
                    $self->attendance->contractId
                )
                ->andReturn(Seq::from($self->examples->contracts[0]));
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createAttendances(),
                    $self->examples->users[1]->id,
                    $self->attendance->contractId
                )
                ->andReturn(Seq::from($self->examples->contracts[3]));
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createAttendances(),
                    $self->attendance->userId,
                    self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq());
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createAttendances(),
                    self::NOT_EXISTING_ID,
                    $self->attendance->contractId
                )
                ->andReturn(Seq::emptySeq());

            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->attendance->assignerId)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->examples->staffs[0]->id)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->examples->staffs[1]->id)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->examples->staffs[24]->id)
                ->andReturn(Seq::from($self->examples->staffs[24]));

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createAttendances()], $self->attendance->officeId)
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createAttendances()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->attendance->userId)
                ->andReturn(Seq::from($self->examples->users[0]));
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), $self->examples->users[1]->id)
                ->andReturn(Seq::from($self->examples->users[1]));
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('return Attendance', function (): void {
            $input = $this->defaultInput();
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $expected = Attendance::create([
                'task' => Task::from($input['task']),
                'serviceCode' => ServiceCode::fromString($input['serviceCode']),
                'userId' => $input['userId'],
                'officeId' => $input['officeId'],
                'assignerId' => $input['assignerId'],
                'assignees' => array_map(
                    fn (array $assignee, int $index): Assignee => Assignee::create([
                        'sort_order' => $index,
                        'staffId' => $assignee['staffId'],
                        'isUndecided' => $assignee['isUndecided'],
                        'isTraining' => $assignee['isTraining'],
                    ]),
                    $input['assignees'],
                    array_keys($input['assignees'])
                ),
                'headcount' => $input['headcount'],
                'schedule' => Schedule::create([
                    'start' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['start']),
                    'end' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['end']),
                    'date' => Carbon::create($input['schedule']['date']),
                ]),
                'durations' => array_map(
                    fn (array $duration): Duration => Duration::create([
                        'activity' => Activity::from($duration['activity']),
                        'duration' => $duration['duration'],
                    ]),
                    $input['durations']
                ),
                'options' => array_map(
                    fn (int $option): ServiceOption => ServiceOption::from($option),
                    $input['options']
                ),
                'note' => $input['note'],
                'isConfirmed' => false,
                'isCanceled' => false,
                'reason' => '',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $this->assertModelStrictEquals($expected, $this->request->payload());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes(), $validator->errors()->toJson());
        });
        $input = $this->defaultInput();

        $examples = [
            'when task is empty' => [
                ['task' => ['入力してください。']],
                ['task' => ''],
                ['task' => Task::ltcsPhysicalCare()->value()],
            ],
            'when unknown task given' => [
                ['task' => ['勤務区分を選択してください。']],
                ['task' => self::NOT_EXISTING_ID],
                ['task' => Task::ltcsPhysicalCare()->value()],
            ],
            'when serviceCode is not string' => [
                ['serviceCode' => ['文字列で入力してください。']],
                ['serviceCode' => 123456],
                ['serviceCode' => '123456'],
            ],
            'when serviceCode is longer than 6' => [
                ['serviceCode' => ['6文字以内で入力してください。']],
                ['serviceCode' => '1234567'],
                ['serviceCode' => '123456'],
            ],
            'when unknown userId given' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when userId not belongs to office given' => [
                ['userId' => ['事業所に所属している利用者を指定してください。']],
                ['userId' => $this->examples->users[1]->id],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when officeId is empty' => [
                [
                    'officeId' => ['入力してください。'],
                ],
                ['officeId' => ''],
                ['officeId' => $this->examples->attendances[0]->officeId],
            ],
            'when unknown officeId given' => [
                [
                    'officeId' => ['正しい値を入力してください。'],
                    'assignerId' => ['事業所に所属しているスタッフを指定してください。'],
                    'assignees.0.staffId' => ['事業所に所属しているスタッフを指定してください。'],
                    'assignees.1.staffId' => ['事業所に所属しているスタッフを指定してください。'],
                ],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->attendances[0]->officeId],
            ],
            'when unknown assignerId given' => [
                ['assignerId' => ['正しい値を入力してください。']],
                ['assignerId' => self::NOT_EXISTING_ID],
                ['assignerId' => $this->examples->attendances[0]->assignerId],
            ],
            'when assignerId not belongs to office given' => [
                ['assignerId' => ['事業所に所属しているスタッフを指定してください。']],
                ['assignerId' => $this->examples->staffs[24]->id],
                ['assignerId' => $this->examples->shifts[0]->assignerId],
            ],
            'when assignees is not array' => [
                [
                    'assignees' => ['配列にしてください。'],
                    'headcount' => ['担当スタッフの数と一致していません。'],
                ],
                [
                    'assignees' => 'error',
                ],
                [
                    'assignees' => [
                        [
                            'staffId' => $this->examples->staffs[0]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                        [
                            'staffId' => $this->examples->staffs[1]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                    ],
                ],
            ],
            'when assignees are duplicated' => [
                [
                    'assignees.0.staffId' => ['それぞれ別の値を入力してください。'],
                    'assignees.1.staffId' => ['それぞれ別の値を入力してください。'],
                ],
                [
                    'assignees' => [
                        [
                            'staffId' => $this->examples->staffs[0]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                        [
                            'staffId' => $this->examples->staffs[0]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                    ],
                ],
                [
                    'assignees' => [
                        [
                            'staffId' => $this->examples->staffs[0]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                        [
                            'staffId' => $this->examples->staffs[1]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                    ],
                ],
            ],
            'when unknown staffId given' => [
                ['assignees.0.staffId' => ['正しい値を入力してください。']],
                [
                    'assignees' => [
                        [
                            'staffId' => self::NOT_EXISTING_ID,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                        [
                            'staffId' => $this->examples->staffs[1]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                    ],
                ],
                [
                    'assignees' => [
                        [
                            'staffId' => $this->examples->staffs[0]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                        [
                            'staffId' => $this->examples->staffs[1]->id,
                            'isUndecided' => false,
                            'isTraining' => false,
                        ],
                    ],
                ],
            ],
            'when headcount is empty' => [
                ['headcount' => ['入力してください。']],
                ['headcount' => ''],
                ['headcount' => 2],
            ],
            'when headcount is not integer' => [
                ['headcount' => ['1〜2の範囲内で入力してください。']],
                ['headcount' => '4'],
                ['headcount' => 2],
            ],
            'when headcount is Numeric value out of range' => [
                ['headcount' => ['1〜2の範囲内で入力してください。']],
                ['headcount' => 3],
                ['headcount' => 2],
            ],
            'when headcount is not equal to assignees count' => [
                ['headcount' => ['担当スタッフの数と一致していません。']],
                ['headcount' => 1],
                ['headcount' => 2],
            ],
            'when start is different from the specified date format' => [
                [
                    'schedule.start' => ['正しい日付を入力してください。'],
                ],
                [
                    'schedule' => [
                        'start' => '10-00-00',
                        'end' => $input['schedule']['end'],
                        'date' => $input['schedule']['date'],
                    ],
                ],
                [
                    'schedule' => [
                        'start' => '10:00',
                        'end' => $input['schedule']['end'],
                        'date' => $input['schedule']['date'],
                    ],
                ],
            ],
            'when end is different from the specified date format' => [
                [
                    'schedule.end' => ['正しい日付を入力してください。'],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => '10-00-00',
                        'date' => $input['schedule']['date'],
                    ],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => '11:00',
                        'date' => $input['schedule']['date'],
                    ],
                ],
            ],
            'when date is not date' => [
                [
                    'schedule.date' => ['正しい日付を入力してください。'],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => $input['schedule']['end'],
                        'date' => '2019-02-30',
                    ],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => $input['schedule']['end'],
                        'date' => $input['schedule']['date'],
                    ],
                ],
            ],
            'when date is after tomorrow' => [
                [
                    'schedule.date' => [Carbon::tomorrow()->toDateString() . 'より前の日付を入力してください。'],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => $input['schedule']['end'],
                        'date' => Carbon::today()->addDay()->toDateString(),
                    ],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => $input['schedule']['end'],
                        'date' => $input['schedule']['date'],
                    ],
                ],
            ],
            'when durations is not equal to schedule' => [
                [
                    'durations' => ['所要時間の合計がスケジュールの時間と一致していません。'],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 120,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when durations have the integrity of the given task' => [
                [
                    'durations' => ['正しい活動内容を入力してください。'],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::other()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when durations is not array' => [
                [
                    'durations' => ['配列にしてください。'],
                ],
                [
                    'durations' => 'error',
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when activity is empty' => [
                ['durations.0.activity' => ['入力してください。']],
                [
                    'durations' => [
                        [
                            'activity' => '',
                            'duration' => 60,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when unknown activity given' => [
                ['durations.0.activity' => ['予実活動内容を指定してください。']],
                [
                    'durations' => [
                        [
                            'activity' => 'error',
                            'duration' => 60,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when duration is empty' => [
                ['durations.0.duration' => ['入力してください。']],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => '',
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when duration is not integer' => [
                ['durations.0.duration' => ['整数で入力してください。']],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 12.2,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when duration is minus value' => [
                ['durations' => ['所要時間の合計がスケジュールの時間と一致していません。'], 'durations.0.duration' => ['0以上で入力してください。']],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => -60,
                        ],
                    ],
                ],
                [
                    'durations' => [
                        [
                            'activity' => Activity::ltcsPhysicalCare()->value(),
                            'duration' => 60,
                        ],
                    ],
                ],
            ],
            'when options is not array' => [
                ['options' => ['配列にしてください。']],
                ['options' => 'error'],
                ['options' => []],
            ],
            'when unknown options given' => [
                ['options.1' => ['サービスオプションを指定してください。']],
                ['options' => [$this->examples->attendances[3]->options[0]->value(), 'error']],
                [
                    'options' => [
                        $this->examples->attendances[3]->options[0]->value(),
                        $this->examples->attendances[3]->options[1]->value(),
                    ],
                ],
            ],
            'when options contain invalid service option for attendance given' => [
                ['options.0' => ['正しいサービスオプションを指定してください。']],
                ['task' => Task::ltcsPhysicalCare()->value(), 'options' => [ServiceOption::sucking()->value()]],
                ['task' => Task::ltcsPhysicalCare()->value(), 'options' => [ServiceOption::notificationEnabled()->value()]],
            ],
            'when note is not string' => [
                ['note' => ['文字列で入力してください。']],
                ['note' => 12345678],
                ['note' => '12345678'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
        $this->should('succeed when options is an empty array', function (): void {
            $validator = $this->request->createValidatorInstance(['options' => []] + $this->defaultInput());
            $this->assertFalse($validator->fails(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $this->should('succeed when options is an array composed of a single ServiceOption', function (): void {
            $validator = $this->request->createValidatorInstance(['options' => [ServiceOption::firstTime()->value()]] + $this->defaultInput());
            $this->assertFalse($validator->fails(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $this->should('succeed when options is an array composed of multiple ServiceOption', function (): void {
            $validator = $this->request->createValidatorInstance([
                'options' => [
                    ServiceOption::firstTime()->value(),
                    ServiceOption::oneOff()->value(),
                ],
            ] + $this->defaultInput());
            $this->assertFalse($validator->fails(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'task' => Task::ltcsPhysicalCare()->value(),
            'serviceCode' => '123456',
            'userId' => $this->attendance->userId,
            'officeId' => $this->attendance->officeId,
            'contractId' => $this->attendance->contractId,
            'assignerId' => $this->attendance->assignerId,
            'assignees' => [
                [
                    'staffId' => $this->examples->staffs[0]->id,
                    'isUndecided' => false,
                    'isTraining' => false,
                ],
                [
                    'staffId' => $this->examples->staffs[1]->id,
                    'isUndecided' => false,
                    'isTraining' => true,
                ],
            ],
            'headcount' => 2,
            'schedule' => [
                'start' => $this->examples->attendances[0]->schedule->start->format('H:i'),
                'end' => $this->examples->attendances[0]->schedule->end->format('H:i'),
                'date' => Carbon::today()->subWeek()->toDateString(),
            ],
            'durations' => [
                [
                    'activity' => Activity::ltcsPhysicalCare()->value(),
                    'duration' => 60,
                ],
            ],
            'options' => [
                ServiceOption::firstTime()->value(),
            ],
            'note' => '備考',
        ];
    }
}
