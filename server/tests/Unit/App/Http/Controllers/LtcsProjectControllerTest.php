<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsProjectController;
use App\Http\Requests\CreateLtcsProjectRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProjectRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\LtcsProjectServiceMenu;
use Domain\Project\Objective;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
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
use Tests\Unit\Mixins\CreateLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\DownloadLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\EditLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsProjectUseCaseMixin;
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
 * {@link \App\Http\Controllers\LtcsProjectController} のテスト.
 */
class LtcsProjectControllerTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use CreateLtcsProjectUseCaseMixin;
    use DownloadLtcsProjectUseCaseMixin;
    use EditLtcsProjectUseCaseMixin;
    use ExamplesConsumer;
    use LookupLtcsProjectServiceMenuUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupLtcsProjectUseCaseMixin;
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

    private array $projectValues = [];
    private LtcsProject $ltcsProject;
    private LtcsProjectController $controller;
    private Response $response;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectControllerTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->projectValues = [
                'project' => $self->examples->ltcsProjects[4],
                'user' => $self->examples->users[0],
                'staff' => $self->examples->staffs[0],
                'office' => $self->examples->offices[0],
                'serviceMenus' => Seq::fromArray($self->examples->ltcsProjectServiceMenus)
                    ->groupBy(fn (LtcsProjectServiceMenu $x): int => $x->id)
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
            $self->createLtcsProjectUseCase
                ->allows('handle')
                ->andReturn($self->ltcsProject)
                ->byDefault();
            $self->downloadLtcsProjectUseCase
                ->allows('handle')
                ->andReturn($self->projectValues)
                ->byDefault();
            $self->editLtcsProjectUseCase
                ->allows('handle')
                ->andReturn($self->ltcsProject)
                ->byDefault();
            $self->lookupLtcsProjectUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->ltcsProject))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupLtcsProjectServiceMenuUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsProjectServiceMenus[0]))
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
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $self->controller = app(LtcsProjectController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/ltcs-projects',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateLtcsProjectRequest::class, function () {
            $request = Mockery::mock(CreateLtcsProjectRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['userId' => $this->ltcsProject->userId])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'], ['userId' => $this->ltcsProject->userId])->getContent()
            );
        });
        $this->should('create LtcsProject using use case', function (): void {
            $this->createLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, $this->ltcsProject->userId, equalTo($this->createLtcsProjectInstance()))
                ->andReturn($this->ltcsProject);

            app()->call([$this->controller, 'create'], ['userId' => $this->ltcsProject->userId]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_download(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/ltcs-projects/{id}/download',
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
                app()->call(
                    [$this->controller, 'download'],
                    ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
                )->getStatusCode()
            );
        });
        $this->should('return a rendered Pdf as string', function (): void {
            $response = app()->call([$this->controller, 'download'], [
                'userId' => $this->ltcsProject->userId,
                'id' => $this->ltcsProject->id,
            ]);

            $this->assertInstanceOf(Response::class, $response);
        });
        $this->should('get values of Project using use case', function () {
            $this->downloadLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, $this->ltcsProject->userId, $this->ltcsProject->id)
                ->andReturn($this->projectValues);

            app()->call(
                [$this->controller, 'download'],
                ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
            );
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/ltcs-projects/{id}',
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
                app()->call(
                    [$this->controller, 'get'],
                    ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of LtcsProject', function (): void {
            $ltcsProject = $this->ltcsProject;
            $response = app()->call([$this->controller, 'get'], [
                'userId' => $this->ltcsProject->userId,
                'id' => $this->ltcsProject->id,
            ]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('ltcsProject')), $response->getContent());
        });
        $this->should('get LtcsProject using use case', function () {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewLtcsProjects(),
                    $this->ltcsProject->userId,
                    $this->ltcsProject->id
                )
                ->andReturn(Seq::from($this->ltcsProject));

            app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewLtcsProjects(),
                    $this->ltcsProject->userId,
                    self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call(
                        [$this->controller, 'get'],
                        ['userId' => $this->ltcsProject->userId, 'id' => self::NOT_EXISTING_ID]
                    );
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
            '/api/users/{userId}/ltcs-projects/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateLtcsProjectRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsProjectRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
                )->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $ltcsProject = $this->ltcsProject;

            $response = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
            );

            $this->assertSame(Json::encode(compact('ltcsProject')), $response->getContent());
        });
        $this->should('update LtcsProject using use case', function (): void {
            $this->editLtcsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ltcsProject->userId,
                    $this->ltcsProject->id,
                    equalTo($this->editLtcsProjectValue())
                )
                ->andReturn($this->ltcsProject);
            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->ltcsProject->userId, 'id' => $this->ltcsProject->id]
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editLtcsProjectValue(): array
    {
        $input = $this->input();
        return [
            'officeId' => $input['officeId'],
            'staffId' => $input['staffId'],
            'writtenOn' => Carbon::parse($input['writtenOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'requestFromUser' => $input['requestFromUser'],
            'requestFromFamily' => $input['requestFromFamily'],
            'problem' => $input['problem'],
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['longTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['longTermObjective']['term']['end']),
                ]),
                'text' => $input['longTermObjective']['text'],
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['shortTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['shortTermObjective']['term']['end']),
                ]),
                'text' => $input['shortTermObjective']['text'],
            ]),
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): LtcsProjectProgram => LtcsProjectProgram::create([
                    'programIndex' => $program['programIndex'],
                    'category' => LtcsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'timeframe' => Timeframe::from($program['timeframe']),
                    'amounts' => Seq::fromArray($program['amounts'])
                        ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]))
                        ->toArray(),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'serviceCode' => ServiceCode::fromString($program['serviceCode']),
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): LtcsProjectContent => LtcsProjectContent::create([
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
     * リクエストから生成されるはずの介護保険サービス計画モデルインスタンス.
     *
     * @return \Domain\Project\LtcsProject
     */
    private function createLtcsProjectInstance(): LtcsProject
    {
        $input = $this->input();
        return LtcsProject::create([
            'officeId' => $input['officeId'],
            'staffId' => $input['staffId'],
            'writtenOn' => Carbon::parse($input['writtenOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'requestFromUser' => $input['requestFromUser'],
            'requestFromFamily' => $input['requestFromFamily'],
            'problem' => $input['problem'],
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['longTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['longTermObjective']['term']['end']),
                ]),
                'text' => $input['longTermObjective']['text'],
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['shortTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['shortTermObjective']['term']['end']),
                ]),
                'text' => $input['shortTermObjective']['text'],
            ]),
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): LtcsProjectProgram => LtcsProjectProgram::create([
                    'programIndex' => $program['programIndex'],
                    'category' => LtcsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'timeframe' => Timeframe::from($program['timeframe']),
                    'amounts' => Seq::fromArray($program['amounts'])
                        ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]))
                        ->toArray(),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'serviceCode' => ServiceCode::fromString($program['serviceCode']),
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): LtcsProjectContent => LtcsProjectContent::create([
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
     * 登録用Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'officeId' => $this->ltcsProject->officeId,
            'staffId' => $this->ltcsProject->staffId,
            'writtenOn' => $this->ltcsProject->writtenOn->toDateString(),
            'effectivatedOn' => $this->ltcsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $this->ltcsProject->requestFromUser,
            'requestFromFamily' => $this->ltcsProject->requestFromFamily,
            'problem' => $this->ltcsProject->problem,
            'longTermObjective' => [
                'term' => [
                    'start' => $this->ltcsProject->longTermObjective->term->start->toDateString(),
                    'end' => $this->ltcsProject->longTermObjective->term->end->toDateString(),
                ],
                'text' => $this->ltcsProject->longTermObjective->text,
            ],
            'shortTermObjective' => [
                'term' => [
                    'start' => $this->ltcsProject->shortTermObjective->term->start->toDateString(),
                    'end' => $this->ltcsProject->shortTermObjective->term->end->toDateString(),
                ],
                'text' => $this->ltcsProject->shortTermObjective->text,
            ],
            'programs' => Seq::fromArray($this->ltcsProject->programs)
                ->map(fn (LtcsProjectProgram $program): array => [
                    'programIndex' => $program->programIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'timeframe' => $program->timeframe->value(),
                    'amounts' => Seq::fromArray($program->amounts)
                        ->map(fn (LtcsProjectAmount $amount): array => [
                            'category' => $amount->category->value(),
                            'amount' => $amount->amount,
                        ])
                        ->toArray(),
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'serviceCode' => $program->serviceCode->toString(),
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (LtcsProjectContent $content): array => [
                            'menuId' => $content->menuId,
                            'duration' => $content->duration,
                            'content' => $content->content,
                            'memo' => $content->memo ?? '',
                        ])
                        ->toArray(),
                    'note' => $program->note,
                ])
                ->toArray(),
        ];
    }
}
