<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsProvisionReportController;
use App\Http\Requests\CreateDwsServiceReportPreviewRequest;
use App\Http\Requests\DeleteDwsProvisionReportRequest;
use App\Http\Requests\FindDwsProvisionReportRequest;
use App\Http\Requests\GetDwsProvisionReportTimeSummaryRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProvisionReportRequest;
use App\Http\Requests\UpdateDwsProvisionReportStatusRequest;
use App\Jobs\CreateDwsServiceReportPreviewJob;
use Closure;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\Shift\ServiceOption;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DeleteDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportTimeSummaryUseCaseMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\GetIndexDwsProvisionReportDigestUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateDwsProvisionReportStatusUseCaseMixin;
use Tests\Unit\Mixins\UpdateDwsProvisionReportUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsProvisionReportController} のテスト.
 */
class DwsProvisionReportControllerTest extends Test
{
    use CarbonMixin;
    use CreateJobUseCaseMixin;
    use ContextMixin;
    use DeleteDwsProvisionReportUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use GetDwsProvisionReportTimeSummaryUseCaseMixin;
    use GetIndexDwsProvisionReportDigestUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use JobsDispatcherMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UpdateDwsProvisionReportUseCaseMixin;
    use UpdateDwsProvisionReportStatusUseCaseMixin;
    use UnitSupport;

    private const FILTER_PARAMS = [
        'officeId' => 1,
        'providedIn' => '2020-10',
    ];
    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];
    private DwsProvisionReport $dwsProvisionReport;
    private DwsProvisionReportController $controller;
    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProvisionReportControllerTest $self): void {
            $self->finderResult = FinderResult::from([], Pagination::create());
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->deleteDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->updateDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn($self->dwsProvisionReport)
                ->byDefault();
            $self->updateDwsProvisionReportStatusUseCase
                ->allows('handle')
                ->andReturn($self->dwsProvisionReport)
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->getIndexDwsProvisionReportDigestUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsProvisionReport))
                ->byDefault();
            $self->getDwsProvisionReportTimeSummaryUseCase
                ->allows('handle')
                ->andReturn([
                    'plan' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                    'result' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                ])
                ->byDefault();

            $self->controller = app(DwsProvisionReportController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createPreview(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-service-report-previews',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'officeId' => $this->dwsProvisionReport->officeId,
                'userId' => $this->dwsProvisionReport->userId,
                'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
            ]),
        ));
        app()->bind(CreateDwsServiceReportPreviewRequest::class, function () {
            $request = Mockery::mock(CreateDwsServiceReportPreviewRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createPreview'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createPreview'])->getContent()
            );
        });
        $this->should('create dws service report preview using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateDwsServiceReportPreviewJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createPreview']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-reports/{officeId}/{userId}/{providedIn}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // ルートパラメーター
                'officeId' => $this->dwsProvisionReport->officeId,
                'userId' => $this->dwsProvisionReport->userId,
                'providedIn' => $this->dwsProvisionReport->providedIn,
            ])
        ));
        app()->bind(DeleteDwsProvisionReportRequest::class, function () {
            $request = Mockery::mock(DeleteDwsProvisionReportRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call(
                    [$this->controller, 'delete'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call(
                    [$this->controller, 'delete'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('delete DwsProvisionReport using use case', function (): void {
            $this->deleteDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    equalTo($this->dwsProvisionReport->providedIn),
                )
                ->andReturnNull();
            app()->call(
                [$this->controller, 'delete'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-reports/{officeId}/{userId}/{providedIn}',
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
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of dwsProvisionReport', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
            $dwsProvisionReport = $this->dwsProvisionReport;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('dwsProvisionReport')), $response->getContent());
        });
        $this->should('get dwsProvisionReport using use case', function (): void {
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    equalTo($this->dwsProvisionReport->providedIn)
                )
                ->andReturn(Option::from($this->examples->dwsProvisionReports));

            app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
        $this->should('return a 204 response when the id not exists in db', function (): void {
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-reports',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindDwsProvisionReportRequest::class, function () {
            $request = Mockery::mock(FindDwsProvisionReportRequest::class)->makePartial();
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
        $this->should('find DwsProvisionReportDigests using use case', function (): void {
            $this->getIndexDwsProvisionReportDigestUseCase
                ->expects('handle')
                ->with($this->context, self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-reports/{officeId}/{userId}/{providedIn}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($this->updateParam())
        ));
        app()->bind(UpdateDwsProvisionReportRequest::class, function () {
            $request = Mockery::mock(UpdateDwsProvisionReportRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $dwsProvisionReport = $this->dwsProvisionReport;
            $this->assertSame(
                Json::encode(compact('dwsProvisionReport')),
                app()->call(
                    [$this->controller, 'update'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('update DwsProvisionReport using use case', function (): void {
            $this->updateDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn->format('Y-m'),
                    $this->payload()
                )
                ->andReturn($this->dwsProvisionReport);
            app()->call(
                [$this->controller, 'update'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_status(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-reports/{officeId}/{userId}/{providedIn}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'officeId' => $this->examples->offices[0]->id,
                'userId' => $this->examples->users[0]->id,
                'status' => DwsProvisionReportStatus::inProgress()->value(),
                'providedIn' => $this->examples->dwsProvisionReports[0]->providedIn->format('Y-m'),
            ])
        ));
        app()->bind(UpdateDwsProvisionReportStatusRequest::class, function () {
            $request = Mockery::mock(UpdateDwsProvisionReportStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'status'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $dwsProvisionReport = $this->dwsProvisionReport;
            $this->assertSame(
                Json::encode(compact('dwsProvisionReport')),
                app()->call(
                    [$this->controller, 'status'],
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ]
                )->getContent()
            );
        });
        $this->should('update status of DwsProvisionReport using use case', function (): void {
            $this->updateDwsProvisionReportStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ['status' => DwsProvisionReportStatus::inProgress()]
                )
                ->andReturn($this->dwsProvisionReport);
            app()->call(
                [$this->controller, 'status'],
                [
                    'officeId' => $this->dwsProvisionReport->officeId,
                    'userId' => $this->dwsProvisionReport->userId,
                    'providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m'),
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getTimeSummary(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-provision-report-time-summary',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->updateParam())
        ));
        app()->bind(GetDwsProvisionReportTimeSummaryRequest::class, function () {
            $request = Mockery::mock(GetDwsProvisionReportTimeSummaryRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getTimeSummary'])->getStatusCode()
            );
        });
        $this->should('return a JSON of time summary', function (): void {
            $this->assertSame(
                Json::encode([
                    'plan' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                    'result' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                ]),
                app()->call([$this->controller, 'getTimeSummary'])->getContent()
            );
        });
        $this->should('get time summary using use case', function (): void {
            $this->getDwsProvisionReportTimeSummaryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    equalTo(Carbon::parse($this->dwsProvisionReport->providedIn->format('Y-m'))),
                    equalTo(Seq::fromArray($this->payload()['plans'])),
                    equalTo(Seq::fromArray($this->payload()['results']))
                )
                ->andReturn([
                    'plan' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                    'result' => [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(1000000),
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::fromInt(2000000),
                        DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::fromInt(3000000),
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::fromInt(4000000),
                        DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::fromInt(5000000),
                        DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::fromInt(6000000),
                    ],
                ]);

            app()->call([$this->controller, 'getTimeSummary']);
        });
    }

    /**
     * 更新用パラメーター生成.
     *
     * @return array
     */
    private function updateParam(): array
    {
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];
        return [
            'plans' => Seq::fromArray($dwsProvisionReport->plans)
                ->map(fn (DwsProvisionReportItem $x): array => [
                    'schedule' => [
                        'date' => $x->schedule->date->toDateString(),
                        'start' => $x->schedule->start->toDateTimeString(),
                        'end' => $x->schedule->end->toDateTimeString(),
                    ],
                    'category' => $x->category->value(),
                    'headcount' => $x->headcount,
                    'options' => Seq::fromArray($x->options)->map(fn (ServiceOption $x): int => $x->value())->toArray(),
                    'note' => $x->note,
                ])
                ->toArray(),
            'results' => Seq::fromArray($dwsProvisionReport->results)
                ->map(fn (DwsProvisionReportItem $x): array => [
                    'schedule' => [
                        'date' => $x->schedule->date->toDateString(),
                        'start' => $x->schedule->start->toDateTimeString(),
                        'end' => $x->schedule->end->toDateTimeString(),
                    ],
                    'category' => $x->category->value(),
                    'headcount' => $x->headcount,
                    'options' => Seq::fromArray($x->options)->map(fn (ServiceOption $x): int => $x->value())->toArray(),
                    'note' => $x->note,
                ])
                ->toArray(),
            // ルートパラメーター
            'officeId' => $dwsProvisionReport->officeId,
            'userId' => $dwsProvisionReport->userId,
            'providedIn' => $dwsProvisionReport->providedIn->format('Y-m'),
        ];
    }

    /**
     * 更新時のペイロード.
     *
     * @return array
     */
    private function payload(): array
    {
        $value = $this->updateParam();
        $plans = Seq::fromArray($value['plans'])
            ->map(fn (array $plan): DwsProvisionReportItem => DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::parse($plan['schedule']['date']),
                    'start' => Carbon::parse($plan['schedule']['start']),
                    'end' => Carbon::parse($plan['schedule']['end']),
                ]),
                'category' => DwsProjectServiceCategory::from($plan['category']),
                'headcount' => $plan['headcount'],
                'options' => Seq::fromArray($plan['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $plan['note'],
            ]));
        $results = Seq::fromArray($value['results'])
            ->map(fn (array $result): DwsProvisionReportItem => DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::parse($result['schedule']['date']),
                    'start' => Carbon::parse($result['schedule']['start']),
                    'end' => Carbon::parse($result['schedule']['end']),
                ]),
                'category' => DwsProjectServiceCategory::from($result['category']),
                'headcount' => $result['headcount'],
                'options' => Seq::fromArray($result['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $result['note'],
            ]));

        return [
            'plans' => $plans->toArray(),
            'results' => $results->toArray(),
        ];
    }
}
