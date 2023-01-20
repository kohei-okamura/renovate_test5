<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsBillingController;
use App\Http\Requests\CreateLtcsBillingRequest;
use App\Http\Requests\FindLtcsBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsBillingStatusRequest;
use App\Jobs\CreateLtcsBillingJob;
use Closure;
use Domain\Billing\LtcsBillingStatus;
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
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\FindLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsBillingInfoUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingStatusUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsBillingController} のテスト.
 */
final class LtcsBillingControllerTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use ExamplesConsumer;
    use FindLtcsBillingUseCaseMixin;
    use GetLtcsBillingInfoUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LtcsProvisionReportFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UpdateLtcsBillingStatusUseCaseMixin;
    use UnitSupport;

    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private array $getInfo;
    private FinderResult $finderResult;
    private LtcsBillingController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getInfo = ['data' => 'aaa'];
            $self->getLtcsBillingInfoUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->ltcsBillings, $pagination);
            $self->findLtcsBillingUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->updateLtcsBillingStatusUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsProvisionReports, Pagination::create()))
                ->byDefault();

            $self->controller = app(LtcsBillingController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-billings',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateLtcsBillingRequest::class, function () {
            $request = Mockery::mock(CreateLtcsBillingRequest::class)->makePartial();
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
                    $this->dispatcher->assertDispatched(CreateLtcsBillingJob::class);
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
            '/api/ltcs-billing-statements/{billingId}/bundles/{billingBundleId}/statements/{id}',
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
                        'id' => $this->examples->ltcsBillings[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $this->getLtcsBillingInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->ltcsBillings[0]->id)
                ->andReturn(['contents' => true]);

            app()->call([$this->controller, 'get'], [
                'id' => $this->examples->ltcsBillings[0]->id,
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
            '/api/ltcs-billings',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->filterParams() + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindLtcsBillingRequest::class, function () {
            $request = Mockery::mock(FindLtcsBillingRequest::class)->makePartial();
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
            $this->findLtcsBillingUseCase
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
            '/api/ltcs-billings/{id}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->statusInput())
        ));
        app()->bind(UpdateLtcsBillingStatusRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsBillingStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return a 202 response when UseCase returns array contains `job`', function (): void {
            $this->updateLtcsBillingStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->ltcsBillings[0]->id,
                    $this->statusPayload(),
                    anInstanceOf(Closure::class)
                )
                ->andReturn(['response' => 'data', 'job' => 'exists']);
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                ],
            );

            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('use UpdateLtcsBillingStatusUseCase', function (): void {
            $this->updateLtcsBillingStatusUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, int $id, LtcsBillingStatus $status, Closure $f): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->ltcsBillings[0]->id, $id);
                    $this->assertSame($this->statusPayload(), $status);
                    $f(Job::create());

                    return $this->getInfo;
                });
            app()->call(
                [$this->controller, 'status'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                ],
            );
        });
    }

    /**
     * 状態更新用の入力値.
     *
     * @return array
     */
    private function statusInput(): array
    {
        return [
            'status' => LtcsBillingStatus::fixed()->value(),
        ];
    }

    /**
     * 状態更新用リクエストの戻り値.
     *
     * @return \Domain\Billing\LtcsBillingStatus
     */
    private function statusPayload(): LtcsBillingStatus
    {
        return LtcsBillingStatus::fixed();
    }

    /**
     * 生成の入力値.
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
            'status' => LtcsBillingStatus::fixed()->value(),
            'officeId' => $this->examples->offices[0]->id,
        ];
    }
}
