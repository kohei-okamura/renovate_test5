<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\AttendanceController;
use App\Http\Requests\BulkCancelAttendanceRequest;
use App\Http\Requests\CancelAttendanceRequest;
use App\Http\Requests\ConfirmAttendanceRequest;
use App\Http\Requests\CreateAttendanceRequest;
use App\Http\Requests\FindAttendanceRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Jobs\CancelAttendanceJob;
use App\Jobs\ConfirmAttendanceJob;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Attendance;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Examples\RoleExample;
use Tests\Unit\Examples\StaffExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\CancelAttendanceUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfirmAttendanceUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateAttendanceUseCaseMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\EditAttendanceUseCaseMixin;
use Tests\Unit\Mixins\FindAttendanceUseCaseMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * AttendanceController のテスト.
 */
class AttendanceControllerTest extends Test
{
    use CancelAttendanceUseCaseMixin;
    use CarbonMixin;
    use ConfirmAttendanceUseCaseMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use CreateAttendanceUseCaseMixin;
    use EditAttendanceUseCaseMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use FindAttendanceUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupAttendanceUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationExample;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use AttendanceRepositoryMixin;
    use RoleExample;
    use RoleRepositoryMixin;
    use StaffExample;
    use StaffResolverMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'date',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];
    public const DELETE_IDS = ['ids' => [2, 3]];
    private const CANCEL_PARAM = [
        'reason' => 'キャンセル理由',
    ];
    private const BULK_CANCEL_PARAM = [
        'ids' => [2, 3],
        'reason' => 'キャンセル理由',
    ];
    private const CONFIRM_IDS = [1, 2];

    private AttendanceController $controller;
    private FinderResult $finderResult;
    private Attendance $attendance;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AttendanceControllerTest $self): void {
            $self->attendance = $self->examples->attendances[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);

            $pagination = Pagination::create(self::PAGINATION_PARAMS);

            $self->finderResult = FinderResult::from($self->examples->attendances, $pagination);

            $self->cancelAttendanceUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->createAttendanceUseCase
                ->allows('handle')
                ->andReturn($self->attendance)
                ->byDefault();

            $self->editAttendanceUseCase
                ->allows('handle')
                ->andReturn($self->attendance)
                ->byDefault();

            $self->findAttendanceUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->confirmAttendanceUseCase
                ->allows('handle')
                ->byDefault();

            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->attendance->copy(['isConfirmed' => false])))
                ->byDefault();
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), anInstanceOf(Permission::class), ...self::DELETE_IDS['ids'])
                ->andReturn(
                    Seq::fromArray([
                        $self->attendance->copy(['isConfirmed' => false]),
                        $self->attendance->copy(['isConfirmed' => false]),
                    ])
                );
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), anInstanceOf(Permission::class), ...self::CONFIRM_IDS)
                ->andReturn(
                    Seq::fromArray([
                        $self->attendance->copy(['isConfirmed' => false]),
                        $self->attendance->copy(['isConfirmed' => false]),
                    ])
                );

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->lookupContractUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->contracts[0]))
                ->byDefault();

            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();

            $self->findContractUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->contracts, $pagination))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->organizationResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->staffs[0]));

            $self->attendanceRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->attendances[0]));

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->attendances[0]));

            $self->controller = app(AttendanceController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkCancel(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances/cancel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::BULK_CANCEL_PARAM),
        ));
        app()->bind(BulkCancelAttendanceRequest::class, function () {
            $request = Mockery::mock(BulkCancelAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'bulkCancel'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'bulkCancel'])->getContent()
            );
        });
        $this->should('cancel Attendance using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CancelAttendanceJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'bulkCancel']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_cancel(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances/{id}/cancel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::CANCEL_PARAM),
        ));
        app()->bind(CancelAttendanceRequest::class, function () {
            $request = Mockery::mock(CancelAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'cancel'], ['id' => $this->examples->attendances[1]->id])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'cancel'], ['id' => $this->examples->attendances[1]->id])->getContent()
            );
        });
        $this->should('cancel Attendance using use case', function (): void {
            $this->cancelAttendanceUseCase
                ->expects('handle')
                ->with($this->context, 'キャンセル理由', $this->examples->attendances[1]->id);

            app()->call([$this->controller, 'cancel'], ['id' => $this->examples->attendances[1]->id]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateAttendanceRequest::class, function () {
            $request = Mockery::mock(CreateAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'])->getContent()
            );
        });
        $this->should('create Attendance using use case', function (): void {
            $this->createAttendanceUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->createAttendanceModelInstance()))
                ->andReturn($this->attendance);

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances/{id}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'get'], ['id' => $this->attendance->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of attendance', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->attendance->id]);

            $attendance = $this->attendance;
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('attendance')), $response->getContent());
        });
        $this->should('get attendance using use case', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewAttendances(), $this->attendance->id)
                ->andReturn(Seq::from($this->attendance));

            app()->call([$this->controller, 'get'], ['id' => $this->attendance->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['id' => self::NOT_EXISTING_ID]);
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateAttendanceRequest::class, function () {
            $request = Mockery::mock(UpdateAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['id' => $this->examples->attendances[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $attendance = $this->examples->attendances[0];

            $response = app()->call([$this->controller, 'update'], ['id' => $attendance->id]);

            $this->assertSame(Json::encode(compact('attendance'), 0), $response->getContent());
        });
        $this->should('update Attendance using use case', function (): void {
            $this->editAttendanceUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->attendances[0]->id, equalTo($this->editAttendanceValue()))
                ->andReturn($this->examples->attendances[0]);

            app()->call([$this->controller, 'update'], ['id' => $this->examples->attendances[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindAttendanceRequest::class, function () {
            $request = Mockery::mock(FindAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn(self::FILTER_PARAMS)->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
        $this->should('find attendances using use case', function (): void {
            $this->findAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::listAttendances(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     */
    public function describe_confirm(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/attendances/confirmation',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['ids' => self::CONFIRM_IDS])
        ));
        app()->bind(ConfirmAttendanceRequest::class, function () {
            $request = Mockery::mock(ConfirmAttendanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'confirm'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'confirm'])->getContent()
            );
        });
        $this->should('confirm Attendance using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(ConfirmAttendanceJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'confirm']);
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editAttendanceValue(): array
    {
        $input = $this->input();
        $assignees = array_map(
            fn (array $assignee, int $index) => Assignee::create([
                'sort_order' => $index,
                'staffId' => $assignee['staffId'],
                'isUndecided' => $assignee['isUndecided'],
                'isTraining' => $assignee['isTraining'],
            ]),
            $input['assignees'],
            array_keys($input['assignees'])
        );
        $durations = array_map(
            fn (array $duration): Duration => Duration::create([
                'activity' => Activity::from($duration['activity']),
                'duration' => $duration['duration'],
            ]),
            $input['durations']
        );
        $options = array_map(fn ($option) => ServiceOption::from($option), $input['options']);
        return [
            'task' => Task::from($input['task']),
            'serviceCode' => ServiceCode::fromString($input['serviceCode']),
            'userId' => $input['userId'],
            'officeId' => $input['officeId'],
            'assignerId' => $input['assignerId'],
            'assignees' => $assignees,
            'headcount' => $input['headcount'],
            'schedule' => Schedule::create([
                'start' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['start']),
                'end' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['end']),
                'date' => Carbon::create($input['schedule']['date']),
            ]),
            'durations' => $durations,
            'options' => $options,
            'note' => $input['note'],
            'updatedAt' => Carbon::now(),
        ];
    }

    /**
     * リクエストから生成されるはずの勤務実績モデルインスタンス.
     *
     * @return \Domain\Shift\Attendance
     */
    private function createAttendanceModelInstance(): Attendance
    {
        $input = $this->input();

        $assignees = array_map(fn ($assignee, $index) => Assignee::create([
            'sort_order' => $index,
            'staffId' => $assignee['staffId'],
            'isUndecided' => $assignee['isUndecided'],
            'isTraining' => $assignee['isTraining'],
        ]), $input['assignees'], array_keys($input['assignees']));

        $durations = array_map(fn ($duration) => Duration::create([
            'activity' => Activity::from($duration['activity']),
            'duration' => $duration['duration'],
        ]), $input['durations']);

        $options = array_map(fn ($option) => ServiceOption::from($option), $input['options']);

        $attendance = [
            'task' => Task::from($input['task']),
            'serviceCode' => ServiceCode::fromString($input['serviceCode']),
            'userId' => $input['userId'],
            'officeId' => $input['officeId'],
            'assignerId' => $input['assignerId'],
            'assignees' => $assignees,
            'headcount' => $input['headcount'],
            'schedule' => Schedule::create([
                'start' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['start']),
                'end' => Carbon::create($input['schedule']['date'] . ' ' . $input['schedule']['end']),
                'date' => Carbon::create($input['schedule']['date']),
            ]),
            'durations' => $durations,
            'options' => $options,
            'note' => $input['note'],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return Attendance::create($attendance);
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function input(): array
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
                'date' => Carbon::today()->toDateString(),
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
