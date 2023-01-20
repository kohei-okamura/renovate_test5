<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsBillingServiceReportController;
use App\Http\Requests\BulkUpdateDwsBillingServiceReportStatusRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingServiceReportStatusRequest;
use App\Jobs\BulkUpdateDwsBillingServiceReportStatusJob;
use Closure;
use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingServiceReportInfoUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateDwsBillingServiceReportStatusUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsBillingServiceReportController} Test.
 */
class DwsBillingServiceReportControllerTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingServiceReportInfoUseCaseMixin;
    use JobsDispatcherMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use UpdateDwsBillingServiceReportStatusUseCaseMixin;

    private array $getInfo;
    private DwsBillingServiceReportController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingServiceReportControllerTest $self): void {
            $self->getInfo = ['data' => 'aaa'];
            $self->getDwsBillingServiceReportInfoUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->updateDwsBillingServiceReportStatusUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->controller = app(DwsBillingServiceReportController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/dws-billing/{dwsBillingId}/bundles/{dwsBillingBundleId}/reports/{id}',
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
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingServiceReports[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'id' => $this->examples->dwsBillingServiceReports[0]->id,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $dwsBillingId = $this->examples->dwsBillings[0]->id;
            $dwsBillingBundleId = $this->examples->dwsBillingBundles[1]->id;
            $reportId = $this->examples->dwsBillingServiceReports[2]->id;
            $this->getDwsBillingServiceReportInfoUseCase
                ->expects('handle')
                ->with($this->context, $dwsBillingId, $dwsBillingBundleId, $reportId)
                ->andReturn(['contents' => true]);

            app()->call([$this->controller, 'get'], [
                'dwsBillingId' => $dwsBillingId,
                'dwsBillingBundleId' => $dwsBillingBundleId,
                'id' => $reportId,
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_status(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/report/{id}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateDwsBillingServiceReportStatusRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingServiceReportStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingServiceReports[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingServiceReports[0]->id,
                ],
            );

            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('update DwsBillingServiceReportStatus using use case', function (): void {
            $this->updateDwsBillingServiceReportStatusUseCase
                ->expects('handle')
                ->andReturnUsing(function (
                    Context $context,
                    int $billingId,
                    int $bundleId,
                    int $id,
                    array $payload
                ): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->dwsBillingServiceReports[0]->dwsBillingId, $billingId);
                    $this->assertSame($this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId, $bundleId);
                    $this->assertSame($this->examples->dwsBillingServiceReports[0]->id, $id);
                    $this->assertEquals($this->payload(), $payload);

                    return ['response' => 'data'];
                })
                ->andReturn();
            app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingServiceReports[0]->id,
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkStatus(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings/{billingId}/service-report-status-update',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->dwsBillingServiceReports[0]->id],
                'status' => DwsBillingStatus::fixed()->value(),
            ])
        ));
        app()->bind(BulkUpdateDwsBillingServiceReportStatusRequest::class, function () {
            $request = Mockery::mock(BulkUpdateDwsBillingServiceReportStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn([
                'ids' => [$this->examples->dwsBillingServiceReports[0]->id],
                'status' => DwsBillingStatus::fixed(),
            ])->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call(
                    [$this->controller, 'bulkStatus'],
                    [
                        'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingServiceReports[0]->id],
                    ],
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call(
                    [$this->controller, 'bulkStatus'],
                    [
                        'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingServiceReports[0]->id],
                    ],
                )->getContent()
            );
        });
        $this->should('bulk update dws report status using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(BulkUpdateDwsBillingServiceReportStatusJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'bulkStatus'],
                [
                    'billingId' => $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                    'ids' => [$this->examples->dwsBillingServiceReports[0]->id],
                ],
            );
        });
    }

    /**
     * 編集用の入力を組み立てる.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'status' => $this->examples->dwsBillingServiceReports[0]->status->value(),
        ];
    }

    /**
     * リクエストクラスの戻り値を返す.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'status' => $this->examples->dwsBillingServiceReports[0]->status,
        ];
    }
}
