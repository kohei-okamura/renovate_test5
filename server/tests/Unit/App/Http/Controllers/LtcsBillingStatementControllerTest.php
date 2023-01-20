<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsBillingStatementController;
use App\Http\Requests\BulkUpdateLtcsBillingStatementStatusRequest;
use App\Http\Requests\RefreshLtcsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsBillingStatementRequest;
use App\Http\Requests\UpdateLtcsBillingStatementStatusRequest;
use App\Jobs\BulkUpdateLtcsBillingStatementStatusJob;
use App\Jobs\RefreshLtcsBillingStatementJob;
use Closure;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Context\Context;
use Domain\Job\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\UpdateLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsBillingStatementController} のテスト.
 */
final class LtcsBillingStatementControllerTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use ExamplesConsumer;
    use GetLtcsBillingStatementInfoUseCaseMixin;
    use JobsDispatcherMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use UpdateLtcsBillingStatementStatusUseCaseMixin;
    use UpdateLtcsBillingStatementUseCaseMixin;

    private const FILTER_PARAMS = [];
    private const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private array $getInfo;
    private LtcsBillingStatementController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getInfo = ['data' => 'aaa'];
            $self->getLtcsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->updateLtcsBillingStatementStatusUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->updateLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->controller = app(LtcsBillingStatementController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkStatus(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-billings/{billingId}/bundles/{billingBundleId}/statements/bulk-status',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                'status' => LtcsBillingStatus::fixed()->value(),
            ])
        ));
        app()->bind(BulkUpdateLtcsBillingStatementStatusRequest::class, function () {
            $request = Mockery::mock(BulkUpdateLtcsBillingStatementStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn([
                'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                'status' => LtcsBillingStatus::fixed(),
            ])->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call(
                    [$this->controller, 'bulkStatus'],
                    [
                        'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                        'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                        'ids' => [$this->examples->ltcsBillingStatements[0]->id],
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
                        'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                        'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                        'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                    ],
                )->getContent()
            );
        });
        $this->should('bulk update ltcs statement status using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(BulkUpdateLtcsBillingStatementStatusJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'bulkStatus'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/ltcs-billings/{billingId}/bundles/{billingBundleId}/statements/{id}',
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
                        'billingId' => $this->examples->ltcsBillings[0]->id,
                        'billingBundleId' => $this->examples->ltcsBillingBundles[0]->id,
                        'id' => $this->examples->ltcsBillingStatements[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'billingId' => $this->examples->ltcsBillings[0]->id,
                    'billingBundleId' => $this->examples->ltcsBillingBundles[0]->id,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $billingId = $this->examples->ltcsBillings[0]->id;
            $billingBundleId = $this->examples->ltcsBillingBundles[1]->id;
            $statementId = $this->examples->ltcsBillingStatements[2]->id;
            $this->getLtcsBillingStatementInfoUseCase
                ->expects('handle')
                ->with($this->context, $billingId, $billingBundleId, $statementId)
                ->andReturn(['contents' => true]);

            app()->call([$this->controller, 'get'], [
                'billingId' => $billingId,
                'billingBundleId' => $billingBundleId,
                'id' => $statementId,
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
            '/api/ltcs-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->statusInput())
        ));
        app()->bind(UpdateLtcsBillingStatementStatusRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsBillingStatementStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('use UpdateLtcsBillingStatementStatusUseCase', function (): void {
            $this->updateLtcsBillingStatementStatusUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, int $billingId, int $bundleId, int $id, LtcsBillingStatus $status, callable $f): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->billingId, $billingId);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->bundleId, $bundleId);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->id, $id);
                    $this->assertEquals($this->statusPayload(), $status);
                    $f($this->examples->ltcsBillings[1]);

                    return $this->getInfo;
                });
            app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
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
            '/api/ltcs-billings/{billingId}/bundles/{billingBundleId}/statements/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->updateInput())
        ));
        app()->bind(UpdateLtcsBillingStatementRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsBillingStatementRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('use UpdateLtcsBillingStatementStatusUseCase', function (): void {
            $this->updateLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, int $billingId, int $bundleId, int $id, array $values): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->billingId, $billingId);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->bundleId, $bundleId);
                    $this->assertSame($this->examples->ltcsBillingStatements[0]->id, $id);
                    $this->assertEquals($this->updatePayload(), $values);

                    return $this->getInfo;
                });
            app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_refresh(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-billings/{billingId}/statement-refresh',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->ltcsBillingStatements[0]->id],
            ])
        ));
        app()->bind(RefreshLtcsBillingStatementRequest::class, function () {
            $request = Mockery::mock(RefreshLtcsBillingStatementRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn([
                'ids' => [$this->examples->ltcsBillingStatements[0]->id],
            ])->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call(
                    [$this->controller, 'refresh'],
                    [
                        'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                        'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                    ],
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call(
                    [$this->controller, 'refresh'],
                    [
                        'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                        'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                    ],
                )->getContent()
            );
        });
        $this->should('refresh ltcs statement using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(RefreshLtcsBillingStatementJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'refresh'],
                [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                ],
            );
        });
    }

    /**
     * 状態編集用の入力を組み立てる.
     *
     * @return array
     */
    private function statusInput(): array
    {
        return [
            'status' => $this->examples->ltcsBillingStatements[0]->status->value(),
        ];
    }

    /**
     * 状態編集リクエストクラスの戻り値を返す.
     *
     * @return LtcsBillingStatus
     */
    private function statusPayload(): LtcsBillingStatus
    {
        return $this->examples->ltcsBillingStatements[0]->status;
    }

    /**
     * 編集用の入力を組み立てる.
     *
     * @return array
     */
    private function updateInput(): array
    {
        return [
            'aggregates' => [
                [
                    'serviceDivisionCode' => LtcsServiceDivisionCode::homeVisitLongTermCare()->value(),
                    'plannedScore' => 0,
                ],
            ],
        ];
    }

    /**
     * 編集リクエストクラスの戻り値を返す.
     *
     * @return LtcsBillingStatus
     */
    private function updatePayload(): array
    {
        return [
            [
                'serviceDivisionCode' => LtcsServiceDivisionCode::homeVisitLongTermCare(),
                'plannedScore' => 0,
            ],
        ];
    }
}
