<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsProjectController;
use App\Http\Requests\CreateDwsProjectRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProjectRequest;
use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Project\DwsProjectServiceMenu;
use Domain\Shift\ServiceOption;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\DownloadDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\EditDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\SnappyMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * DwsProjectController のテスト.
 */
class DwsProjectControllerTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use CreateDwsProjectUseCaseMixin;
    use DownloadDwsProjectUseCaseMixin;
    use EditDwsProjectUseCaseMixin;
    use ExamplesConsumer;
    use LookupContractUseCaseMixin;
    use LookupDwsProjectServiceMenuUseCaseMixin;
    use LookupDwsProjectUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use SnappyMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private array $projectValues;
    private DwsProject $dwsProject;
    private DwsProjectController $controller;
    private Response $response;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProjectControllerTest $self): void {
            $self->dwsProject = $self->examples->dwsProjects[0];
            $self->projectValues = [
                'project' => $self->examples->dwsProjects[4],
                'user' => $self->examples->users[0],
                'staff' => $self->examples->staffs[0],
                'office' => $self->examples->offices[0],
                'serviceMenus' => Seq::fromArray($self->examples->dwsProjectServiceMenus)
                    ->groupBy(fn (DwsProjectServiceMenu $x): int => $x->id)
                    ->toAssoc(),
            ];
            $self->response = new Response();
            $self->snappy
                ->allows('setOption')
                ->andReturnSelf()
                ->byDefault();
            $self->snappy
                ->allows('loadHTML')
                ->andReturnSelf()
                ->byDefault();
            $self->snappy
                ->allows('download')
                ->andReturn($self->response)
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn('xxxx.pdf')
                ->byDefault();
            $self->createDwsProjectUseCase
                ->allows('handle')
                ->andReturn($self->dwsProject)
                ->byDefault();
            $self->downloadDwsProjectUseCase
                ->allows('handle')
                ->andReturn($self->projectValues)
                ->byDefault();
            $self->editDwsProjectUseCase
                ->allows('handle')
                ->andReturn($self->dwsProject)
                ->byDefault();
            $self->lookupDwsProjectUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsProjects[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupDwsProjectServiceMenuUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsProjectServiceMenus[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(DwsProjectController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/dws-projects',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateDwsProjectRequest::class, function () {
            $request = Mockery::mock(CreateDwsProjectRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['userId' => $this->dwsProject->userId])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'], ['userId' => $this->dwsProject->userId])->getContent()
            );
        });
        $this->should('create DwsProject using use case', function (): void {
            $this->createDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsProject->userId, equalTo($this->createDwsProjectInstance()))
                ->andReturn($this->dwsProject);

            app()->call([$this->controller, 'create'], ['userId' => $this->dwsProject->userId]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_download(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/dws-projects/{id}/download',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'download'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id])->getStatusCode()
            );
        });
        $this->should('return a rendered Pdf as string', function (): void {
            $response = app()->call([$this->controller, 'download'], [
                'userId' => $this->dwsProject->userId,
                'id' => $this->dwsProject->id,
            ]);

            $this->assertInstanceOf(Response::class, $response);
        });
        $this->should('get values of Project using use case', function () {
            $this->downloadDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsProject->userId, $this->dwsProject->id)
                ->andReturn($this->projectValues);

            app()->call([$this->controller, 'download'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/dws-projects/{id}',
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
                app()->call([$this->controller, 'get'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of DwsProject', function (): void {
            $dwsProject = $this->dwsProject;

            $response = app()->call([$this->controller, 'get'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(JSON::encode(compact('dwsProject')), $response->getContent());
        });
        $this->should('get DwsProject using use case', function (): void {
            $this->lookupDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, $this->dwsProject->id)
                ->andReturn(Seq::from($this->dwsProject));

            app()->call([$this->controller, 'get'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['userId' => $this->dwsProject->userId, 'id' => self::NOT_EXISTING_ID]);
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
            '/api/users/{userId}/dws-projects/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateDwsProjectRequest::class, function () {
            $request = Mockery::mock(UpdateDwsProjectRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->dwsProjects[0]->userId, 'id' => $this->examples->dwsProjects[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $dwsProject = $this->dwsProject;

            $response = app()->call([$this->controller, 'update'], ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id]);

            $this->assertSame(Json::encode(compact('dwsProject')), $response->getContent());
        });
        $this->should('update DwsProject using use case', function (): void {
            $this->editDwsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->dwsProject->userId,
                    $this->dwsProject->id,
                    equalTo($this->payload())
                )
                ->andReturn($this->dwsProject);
            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->dwsProject->userId, 'id' => $this->dwsProject->id]
            );
        });
    }

    /**
     * リクエストから生成されるはずの障害福祉サービス計画モデルインスタンス.
     *
     * @return \Domain\Project\DwsProject
     */
    private function createDwsProjectInstance(): DwsProject
    {
        $input = $this->input();
        return DwsProject::create([
            'officeId' => $input['officeId'],
            'staffId' => $input['staffId'],
            'writtenOn' => Carbon::parse($input['writtenOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'requestFromUser' => $input['requestFromUser'],
            'requestFromFamily' => $input['requestFromFamily'],
            'objective' => $input['objective'],
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): DwsProjectProgram => DwsProjectProgram::create([
                    'summaryIndex' => $program['summaryIndex'],
                    'category' => DwsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): DwsProjectContent => DwsProjectContent::create([
                            'menuId' => $content['menuId'],
                            'duration' => $content['duration'],
                            'content' => $content['content'],
                            'memo' => $content['memo'] ?? '',
                        ]))
                        ->toArray(),
                    'note' => $program['note'] ?? '',
                ]))
                ->toArray(),
        ]);
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->input();
        return [
            'officeId' => $input['officeId'],
            'staffId' => $input['staffId'],
            'writtenOn' => Carbon::parse($input['writtenOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'requestFromUser' => $input['requestFromUser'],
            'requestFromFamily' => $input['requestFromFamily'],
            'objective' => $input['objective'],
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): DwsProjectProgram => DwsProjectProgram::create([
                    'summaryIndex' => $program['summaryIndex'],
                    'category' => DwsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): DwsProjectContent => DwsProjectContent::create([
                            'menuId' => $content['menuId'],
                            'duration' => $content['duration'],
                            'content' => $content['content'],
                            'memo' => $content['memo'] ?? '',
                        ]))
                        ->toArray(),
                    'note' => $program['note'] ?? '',
                ]))
                ->toArray(),
        ];
    }

    /**
     * 登録用 or 更新用Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'officeId' => $this->dwsProject->officeId,
            'staffId' => $this->dwsProject->staffId,
            'writtenOn' => $this->dwsProject->writtenOn->toDateString(),
            'effectivatedOn' => $this->dwsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $this->dwsProject->requestFromUser,
            'requestFromFamily' => $this->dwsProject->requestFromFamily,
            'objective' => $this->dwsProject->objective,
            'programs' => Seq::fromArray($this->dwsProject->programs)
                ->map(fn (DwsProjectProgram $program): array => [
                    'summaryIndex' => $program->summaryIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (DwsProjectContent $content): array => [
                            'menuId' => $content->menuId,
                            'duration' => $content->duration,
                            'content' => $content->content,
                            'memo' => $content->memo,
                        ])
                        ->toArray(),
                    'note' => $program->note,
                ])
                ->toArray(),
        ];
    }
}
