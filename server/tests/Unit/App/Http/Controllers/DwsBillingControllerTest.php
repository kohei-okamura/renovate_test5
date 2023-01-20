<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsBillingController;
use App\Http\Requests\CopyDwsBillingRequest;
use App\Http\Requests\CreateDwsBillingRequest;
use App\Http\Requests\FindDwsBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatusRequest;
use App\Jobs\CopyDwsBillingJob;
use App\Jobs\CreateDwsBillingJob;
use Closure;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\FindDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingInfoUseCseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateDwsBillingStatusUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsBillingController} Test.
 */
class DwsBillingControllerTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DwsProvisionReportFinderMixin;
    use ExamplesConsumer;
    use FindDwsBillingUseCaseMixin;
    use GetDwsBillingInfoUseCseMixin;
    use JobsDispatcherMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UpdateDwsBillingStatusUseCaseMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private array $getInfo;
    private FinderResult $finderResult;
    private DwsBillingController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingControllerTest $self): void {
            $self->getInfo = ['data' => 'aaa'];
            $self->getDwsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->dwsBillings, $pagination);
            $self->findDwsBillingUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->updateDwsBillingStatusUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), $self->examples->dwsBillings[1]->id)
                ->andReturn(Seq::from($self->examples->dwsBillings[1]->copy(['status' => DwsBillingStatus::fixed()])))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->dwsProvisionReports, Pagination::create()))
                ->byDefault();

            $self->controller = app(DwsBillingController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateDwsBillingRequest::class, function () {
            $request = Mockery::mock(CreateDwsBillingRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'create'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'create'])->getContent()
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
                    $this->dispatcher->assertDispatched(CreateDwsBillingJob::class);
                    return $this->examples->jobs[0];
                });

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
            '/api/dws-billing-statements/{billingId}/bundles/{billingBundleId}/statements/{id}',
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
                app()->call(
                    [$this->controller, 'get'],
                    [
                        'id' => $this->examples->dwsBillings[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'id' => $this->examples->dwsBillings[0]->id,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $this->getDwsBillingStatementInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->dwsBillings[0]->id)
                ->andReturn(['contents' => true]);

            app()->call([$this->controller, 'get'], [
                'id' => $this->examples->dwsBillings[0]->id,
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->filterParams() + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindDwsBillingRequest::class, function () {
            $request = Mockery::mock(FindDwsBillingRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn($this->filterParams())->byDefault();
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
        $this->should('find Offices using use case', function (): void {
            $this->findDwsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listBillings(),
                    $this->filterParams(),
                    self::PAGINATION_PARAMS
                )
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_status(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings/{id}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateDwsBillingStatusRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->dwsBillings[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return a 202 response when UseCase returns array contains `job`', function (): void {
            $this->updateDwsBillingStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->payload(),
                    anInstanceOf(Closure::class)
                )
                ->andReturn(['response' => 'data', 'job' => 'exists']);
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->dwsBillings[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->dwsBillings[0]->id,
                ],
            );

            $this->assertSame(Json::encode(['response' => 'data']), $response->getContent());
        });
        $this->should('use UpdateDwsBillingStatusUseCase', function (): void {
            $this->updateDwsBillingStatusUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, int $id, DwsBillingStatus $status, Closure $f): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->dwsBillings[0]->id, $id);
                    $this->assertSame($this->payload(), $status);
                    $f(Job::create());

                    return ['response' => 'data'];
                });
            app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->dwsBillings[0]->id,
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_copy(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings/{id}/copy',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['id' => $this->examples->dwsBillings[1]->id])
        ));
        app()->bind(CopyDwsBillingRequest::class, function () {
            $request = Mockery::mock(CopyDwsBillingRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call(
                    [$this->controller, 'copy'],
                    [
                        'id' => $this->examples->dwsBillings[1]->id,
                    ],
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call(
                    [$this->controller, 'copy'],
                    [
                        'id' => $this->examples->dwsBillings[1]->id,
                    ],
                )->getContent()
            );
        });
        $this->should('copy dws billing using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CopyDwsBillingJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'copy'],
                [
                    'id' => $this->examples->dwsBillings[1]->id,
                ],
            );
        });
    }

    /**
     * 検索項目の定義.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'start' => '2021-01',
            'end' => '2021-02',
            'status' => DwsBillingStatus::fixed(),
            'officeId' => $this->examples->offices[0]->id,
        ];
    }

    /**
     * 状態更新用の入力値.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'status' => DwsBillingStatus::fixed()->value(),
        ];
    }

    /**
     * 状態更新用リクエストの戻り値.
     *
     * @return \Domain\Billing\DwsBillingStatus
     */
    private function payload(): DwsBillingStatus
    {
        return DwsBillingStatus::fixed();
    }

    /**
     * 生成用の入力値.
     *
     * @return array
     */
    private function inputCreate(): array
    {
        return [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2020-01',
        ];
    }
}
