<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateShiftRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ContractExample;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OfficeExample;
use Tests\Unit\Examples\OfficeGroupExample;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Examples\RoleExample;
use Tests\Unit\Examples\ShiftExample;
use Tests\Unit\Examples\StaffExample;
use Tests\Unit\Examples\UserExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UpdateShiftRequest のテスト.
 */
class UpdateShiftRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use FindShiftUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use ContractExample;
    use UnitSupport;
    use LookupContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupShiftUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use OrganizationExample;
    use OfficeExample;
    use OfficeGroupExample;
    use UserExample;
    use SessionMixin;
    use ShiftExample;
    use StaffResolverMixin;
    use StaffExample;
    use RoleExample;

    protected UpdateShiftRequest $request;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateShiftRequestTest $self): void {
            $self->request = new UpdateShiftRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->shift = $self->examples->shifts[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
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
                ->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), 2, \Mockery::any(), \Mockery::any())
                ->andReturn(Option::none())
                ->byDefault();
            $self->findShiftUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->shifts, Pagination::create(['all' => true])));

            $self->lookupContractUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->userId, $self->shift->contractId)
                ->andReturn(Seq::from($self->examples->contracts[0]));
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->users[1]->id, $self->shift->contractId)
                ->andReturn(Seq::from($self->examples->contracts[3]));
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupContractUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), self::NOT_EXISTING_ID, $self->shift->contractId)
                ->andReturn(Seq::emptySeq());

            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->assignerId)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->staffs[0]->id)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->staffs[1]->id)
                ->andReturn(Seq::from($self->examples->staffs[0]));
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->staffs[24]->id)
                ->andReturn(Seq::from($self->examples->staffs[24]));

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateShifts()], $self->shift->officeId)
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateShifts()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->userId)
                ->andReturn(Seq::from($self->examples->users[0]));
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->users[1]->id)
                ->andReturn(Seq::from($self->examples->users[1]));
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->shift->id)
                ->andReturn(Seq::from($self->shift))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[11]->id)
                ->andReturn(Seq::from($self->examples->shifts[11]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('return array of Shift', function (): void {
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
            $expected = [
                'task' => Task::from($input['task']),
                'serviceCode' => ServiceCode::fromString($input['serviceCode']),
                'userId' => $input['userId'],
                'officeId' => $input['officeId'],
                'assignerId' => $input['assignerId'],
                'assignees' => Seq::fromArray($input['assignees'])
                    ->map(fn (array $assignee): Assignee => Assignee::create([
                        'staffId' => $assignee['isUndecided']
                            ? null
                            : ($assignee['staffId'] ?? null),
                        'isUndecided' => $assignee['isUndecided'],
                        'isTraining' => $assignee['isTraining'],
                    ]))
                    ->toArray(),
                'headcount' => $input['headcount'],
                'schedule' => Schedule::create([
                    'start' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['start']),
                    'end' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['end']),
                    'date' => Carbon::create($input['schedule']['date']),
                ]),
                'durations' => Seq::fromArray($input['durations'])
                    ->map(fn (array $duration): Duration => Duration::create([
                        'activity' => Activity::from($duration['activity']),
                        'duration' => $duration['duration'],
                    ]))
                    ->toArray(),
                'options' => Seq::fromArray($input['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $input['note'],
                'updatedAt' => Carbon::now(),
            ];
            $this->assertEquals($expected, $this->request->payload());
        });
        $this->should('return Shift with minimum parameters', function (): void {
            $input = [
                'task' => Task::other()->value(),
                'officeId' => $this->shift->officeId,
                'assignerId' => $this->shift->assignerId,
                'assignees' => [
                    [
                        'isUndecided' => false,
                        'isTraining' => false,
                    ],
                    [
                        'isUndecided' => false,
                        'isTraining' => true,
                    ],
                ],
                'headcount' => 2,
                'schedule' => [
                    'start' => $this->examples->shifts[0]->schedule->start->format('H:i'),
                    'end' => $this->examples->shifts[0]->schedule->end->format('H:i'),
                    'date' => '2040-11-12',
                ],
                'durations' => [
                    [
                        'activity' => Activity::ltcsPhysicalCare()->value(),
                        'duration' => 60,
                    ],
                ],
            ];
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $expected = [
                'task' => Task::from($input['task']),
                'serviceCode' => null,
                'userId' => null,
                'officeId' => $input['officeId'],
                'assignerId' => $input['assignerId'],
                'assignees' => array_map(
                    fn (array $assignee, int $index): Assignee => Assignee::create([
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
                'options' => [],
                'note' => '',
                'updatedAt' => Carbon::now(),
            ];
            $this->assertEquals($expected, $this->request->payload());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->passes());
        });
        $this->should('succeed when LookupShiftUseCase return empty', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $this->shift->id)
                ->andReturn(Seq::emptySeq());
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->passes());
        });
        $yesterday = Carbon::yesterday()->format('Y-m-d');
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
                ['officeId' => $this->examples->shifts[0]->officeId],
            ],
            'when unknown officeId given' => [
                [
                    'officeId' => ['正しい値を入力してください。'],
                    'assignerId' => ['事業所に所属しているスタッフを指定してください。'],
                    'assignees.0.staffId' => ['事業所に所属しているスタッフを指定してください。'],
                    'assignees.1.staffId' => ['事業所に所属しているスタッフを指定してください。'],
                ],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->shifts[0]->officeId],
            ],
            'when unknown assignerId given' => [
                ['assignerId' => ['正しい値を入力してください。']],
                ['assignerId' => self::NOT_EXISTING_ID],
                ['assignerId' => $this->examples->shifts[0]->assignerId],
            ],
            'when assignerId not belongs to office given' => [
                ['assignerId' => ['事業所に所属しているスタッフを指定してください。']],
                ['assignerId' => $this->examples->staffs[24]->id],
                ['assignerId' => $this->examples->shifts[0]->assignerId],
            ],
            'when assignees is not array' => [
                ['assignees' => ['配列にしてください。'], 'headcount' => ['担当スタッフの数と一致していません。']],
                ['assignees' => 'error'],
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
            'when the staff with the given staffId is not available' => [
                [
                    'assignees.0.staffId' => ['日時が重複しています。'],
                    'assignees.1.staffId' => ['日時が重複しています。'],
                ],
                [
                    'start' => '2040-11-12T11:00:00+0900',
                    'end' => '2040-11-12T12:00:00+0900',
                ],
                [
                    'start' => '2040-11-12T09:00:00+0900',
                    'end' => '2040-11-12T10:00:00+0900',
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
                        'date' => '2099-02-30',
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
            'when date is before today' => [
                [
                    'schedule.date' => ['今日以降の日付を入力してください。'],
                ],
                [
                    'schedule' => [
                        'start' => $input['schedule']['start'],
                        'end' => $input['schedule']['end'],
                        'date' => $yesterday,
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
                ['options' => [$this->examples->shifts[3]->options[0]->value(), 'error']],
                ['options' => [$this->examples->shifts[3]->options[0]->value(), $this->examples->shifts[3]->options[1]->value()]],
            ],
            'when options contain invalid service option for shift given' => [
                ['options.0' => ['正しいサービスオプションを指定してください。']],
                ['task' => Task::ltcsPhysicalCare()->value(), 'options' => [ServiceOption::sucking()->value()]],
                ['task' => Task::ltcsPhysicalCare()->value(), 'options' => [ServiceOption::notificationEnabled()->value()]],
            ],
            'when note is not string' => [
                ['note' => ['文字列で入力してください。']],
                ['note' => 12345678],
                ['note' => '12345678'],
            ],
            'when schedule.start of entity is less than now' => [
                ['id' => ['過去の勤務シフトは編集できません。']],
                ['id' => $this->examples->shifts[11]->id],
                ['id' => $this->shift->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            // ルートパラメーター
            'id' => $this->shift->id,
            'task' => Task::ltcsPhysicalCare()->value(),
            'serviceCode' => '123456',
            'userId' => $this->shift->userId,
            'officeId' => $this->shift->officeId,
            'assignerId' => $this->shift->assignerId,
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
                'start' => $this->examples->shifts[0]->schedule->start->format('H:i'),
                'end' => $this->examples->shifts[0]->schedule->end->format('H:i'),
                'date' => '2040-11-12',
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
