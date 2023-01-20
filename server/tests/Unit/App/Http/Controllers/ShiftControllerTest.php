<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\ShiftController;
use App\Http\Requests\BulkCancelShiftRequest;
use App\Http\Requests\CancelShiftRequest;
use App\Http\Requests\ConfirmShiftRequest;
use App\Http\Requests\CreateShiftRequest;
use App\Http\Requests\CreateShiftTemplateRequest;
use App\Http\Requests\FindShiftRequest;
use App\Http\Requests\ImportShiftRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Jobs\CancelShiftJob;
use App\Jobs\ConfirmShiftJob;
use App\Jobs\CreateShiftTemplateJob;
use App\Jobs\ImportShiftJob;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CancelShiftUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfirmShiftUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\CreateJobWithFileUseCaseMixin;
use Tests\Unit\Mixins\CreateShiftUseCaseMixin;
use Tests\Unit\Mixins\EditShiftUseCaseMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * ShiftController のテスト.
 */
class ShiftControllerTest extends Test
{
    use CancelShiftUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ConfirmShiftUseCaseMixin;
    use CreateJobUseCaseMixin;
    use CreateJobWithFileUseCaseMixin;
    use CreateShiftUseCaseMixin;
    use EditShiftUseCaseMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use FindContractUseCaseMixin;
    use FindShiftUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupShiftUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use ShiftRepositoryMixin;
    use RoleRepositoryMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private const FILTER_PARAMS = [];
    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];
    private const CANCEL_PARAM = [
        'id' => 1,
        'reason' => 'キャンセル理由',
    ];
    private const BULK_CANCEL_PARAM = [
        'ids' => [2, 3],
        'reason' => 'キャンセル理由',
    ];
    private const CONFIRM_IDS = ['ids' => [2, 3]];
    private const DIR = 'artifacts';
    private const FILENAME = 'dummy.pdf';
    private $resource;

    private ShiftController $controller;
    private FinderResult $finderResult;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftControllerTest $self): void {
            $self->shift = $self->examples->shifts[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);

            $pagination = Pagination::create(self::PAGINATION_PARAMS);

            $self->finderResult = FinderResult::from($self->examples->shifts, $pagination);

            $self->createShiftUseCase
                ->allows('handle')
                ->andReturn($self->shift)
                ->byDefault();

            $self->editShiftUseCase
                ->allows('handle')
                ->andReturn($self->shift)
                ->byDefault();

            $self->findShiftUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();

            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->shift->copy(['isConfirmed' => false])))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), anInstanceOf(Permission::class), ...self::CONFIRM_IDS['ids'])
                ->andReturn(
                    Seq::from(
                        $self->shift->copy(['isConfirmed' => false]),
                        $self->shift->copy(['isConfirmed' => false])
                    )
                )
                ->byDefault();

            $self->confirmShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->shift->copy(['isConfirmed' => true]),
                    $self->shift->copy(['isConfirmed' => true])
                ))
                ->byDefault();

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

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->createJobWithFileUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->resource = tmpfile();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->shifts[0]));

            $self->cancelShiftUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->controller = app(ShiftController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateShiftRequest::class, function () {
            $request = Mockery::mock(CreateShiftRequest::class)->makePartial();
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
        $this->should('create Shift using use case', function (): void {
            $this->createShiftUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->createShiftModelInstance()))
                ->andReturn($this->shift);

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->shift->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of shift', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->shift->id]);
            $shift = $this->shift;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('shift')), $response->getContent());
        });
        $this->should('get shift using use case', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewShifts(), $this->shift->id)
                ->andReturn(Seq::from($this->shift));

            app()->call([$this->controller, 'get'], ['id' => $this->shift->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewShifts(), self::NOT_EXISTING_ID)
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
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateShiftRequest::class, function () {
            $request = Mockery::mock(UpdateShiftRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->examples->shifts[0]->id])->getStatusCode()
            );
        });
        $this->should('return an response of Entity', function (): void {
            $shift = $this->examples->shifts[0];

            $response = app()->call([$this->controller, 'update'], ['id' => $shift->id]);

            $this->assertSame(Json::encode(compact('shift'), 0), $response->getContent());
        });
        $this->should('update Shift using use case', function (): void {
            $this->editShiftUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->shifts[0]->id, equalTo($this->editShiftValue()))
                ->andReturn($this->examples->shifts[0]);

            app()->call([$this->controller, 'update'], ['id' => $this->examples->shifts[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_confirm(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts/confirmation',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::CONFIRM_IDS)
        ));
        app()->bind(ConfirmShiftRequest::class, function () {
            $request = Mockery::mock(ConfirmShiftRequest::class)->makePartial();
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
        $this->should('confirm Shift using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(ConfirmShiftJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'confirm']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/office-groups',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindShiftRequest::class, function () {
            $request = Mockery::mock(FindShiftRequest::class)->makePartial();
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
        $this->should('find shifts using use case', function (): void {
            $this->findShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::listShifts(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createTemplate(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shift-templates',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputTemplate())
        ));
        app()->bind(CreateShiftTemplateRequest::class, function () {
            $request = Mockery::mock(CreateShiftTemplateRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createTemplate'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createTemplate'])->getContent()
            );
        });
        $this->should('create template using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateShiftTemplateJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createTemplate']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_import(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shift-imports',
            'POST',
            [],
            [],
            ['file' => UploadedFile::fake()->create('example.xlsx')],
            ['CONTENT_TYPE' => 'multipart/form-data'],
        ));
        app()->bind(ImportShiftRequest::class, function () {
            $request = Mockery::mock(ImportShiftRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'import'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'import'])->getContent()
            );
        });
        $this->should('import shifts using use case', function (): void {
            $this->createJobWithFileUseCase
                ->expects('handle')
                // FileInputStream::fromFile() は、ランダムファイル名を生成するので、検証しない
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any(), Mockery::any())
                ->andReturnUsing(function (Context $context, FileInputStream $stream, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0], 'DummyPath');
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(ImportShiftJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'import']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkCancel(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts/cancel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::BULK_CANCEL_PARAM),
        ));
        app()->bind(BulkCancelShiftRequest::class, function () {
            $request = Mockery::mock(BulkCancelShiftRequest::class)->makePartial();
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
        $this->should('cancel Shift using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CancelShiftJob::class);
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
        app()->bind('request', fn () => LumenRequest::create(
            '/api/shifts/{id}/cancel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::CANCEL_PARAM),
        ));
        app()->bind(CancelShiftRequest::class, function () {
            $request = Mockery::mock(CancelShiftRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'cancel'], ['id' => $this->examples->shifts[1]->id])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'cancel'], ['id' => $this->examples->shifts[1]->id])->getContent()
            );
        });
        $this->should('cancel Shift using use case', function (): void {
            $this->cancelShiftUseCase
                ->expects('handle')
                ->with($this->context, 'キャンセル理由', $this->examples->shifts[1]->id);

            app()->call([$this->controller, 'cancel'], ['id' => $this->examples->shifts[1]->id]);
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editShiftValue(): array
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
     * リクエストから生成されるはずの勤務シフトモデルインスタンス.
     *
     * @return \Domain\Shift\Shift
     */
    private function createShiftModelInstance(): Shift
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

        $shift = [
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
        return Shift::create($shift);
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            // ルートパラメータ
            'id' => $this->shift->id,
            'task' => Task::ltcsPhysicalCare()->value(),
            'serviceCode' => '123456',
            'userId' => $this->shift->userId,
            'officeId' => $this->shift->officeId,
            'contractId' => $this->shift->contractId,
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

    /**
     * 雛形生成input.
     *
     * @return array リクエストパラメータ
     */
    private function inputTemplate(): array
    {
        return [
            'officeId' => $this->examples->offices[0]->id,
            'isCopy' => true,
            'source' => [
                'start' => $this->examples->shifts[0]->schedule->start->toDateString(),
                'end' => $this->examples->shifts[0]->schedule->end->toDateString(),
            ],
            'range' => [
                'start' => $this->examples->shifts[0]->schedule->start->addWeek()->toDateString(),
                'end' => $this->examples->shifts[0]->schedule->end->addWeek()->toDateString(),
            ],
        ];
    }
}
