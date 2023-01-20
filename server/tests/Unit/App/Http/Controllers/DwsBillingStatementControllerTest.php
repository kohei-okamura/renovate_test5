<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsBillingStatementController;
use App\Http\Requests\BulkUpdateDwsBillingStatementStatusRequest;
use App\Http\Requests\RefreshDwsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationStatusRequest;
use App\Http\Requests\UpdateDwsBillingStatementRequest;
use App\Http\Requests\UpdateDwsBillingStatementStatusRequest;
use App\Jobs\BulkUpdateDwsBillingStatementStatusJob;
use App\Jobs\RefreshDwsBillingStatementJob;
use Closure;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Context\Context;
use Domain\Job\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\EditDwsBillingStatementStatusUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingStatementInfoUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateDwsBillingStatementCopayCoordinationStatusUseCaseMixin;
use Tests\Unit\Mixins\UpdateDwsBillingStatementCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\UpdateDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsBillingStatementController} Test.
 */
class DwsBillingStatementControllerTest extends Test
{
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use EditDwsBillingStatementStatusUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingStatementInfoUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use UpdateDwsBillingStatementCopayCoordinationUseCaseMixin;
    use UpdateDwsBillingStatementCopayCoordinationStatusUseCaseMixin;
    use UpdateDwsBillingStatementUseCaseMixin;

    private array $getInfo;
    private DwsBillingStatementController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingStatementControllerTest $self): void {
            $self->getInfo = ['data' => 'aaa'];
            $self->getDwsBillingStatementInfoUseCase
                ->allows('handle')
                ->andReturn($self->getInfo)
                ->byDefault();

            $self->editDwsBillingStatementStatusUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

            $self->updateDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

            $self->updateDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->updateDwsBillingStatementCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->updateDwsBillingStatementCopayCoordinationStatusUseCase
                ->allows('handle')
                ->andReturn(['response' => 'data'])
                ->byDefault();

            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->controller = app(DwsBillingStatementController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkStatus(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings/{billingId}/statement-status-update',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
                'status' => DwsBillingStatus::fixed()->value(),
            ])
        ));
        app()->bind(BulkUpdateDwsBillingStatementStatusRequest::class, function () {
            $request = Mockery::mock(BulkUpdateDwsBillingStatementStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn([
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
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
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
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
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
                    ],
                )->getContent()
            );
        });
        $this->should('bulk update dws statement status using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(BulkUpdateDwsBillingStatementStatusJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'bulkStatus'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'ids' => [$this->examples->dwsBillingStatements[0]->id],
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_copayCoordination(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/copay-coordination',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->copayCoordinationInput())
        ));
        app()->bind(UpdateDwsBillingStatementCopayCoordinationRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingStatementCopayCoordinationRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'copayCoordination'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'copayCoordination'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode(['response' => 'data']), $response->getContent());
        });
        $this->should('update Entity using use case', function (): void {
            $this->updateDwsBillingStatementCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    $this->examples->dwsBillingStatements[0]->id,
                    equalTo(Option::some($this->copayCoordinationPayload()))
                )
                ->andReturn(['response' => 'data']);
            app()->call(
                [$this->controller, 'copayCoordination'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_copayCoordinationStatus(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/copay-coordination-status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value()])
        ));
        app()->bind(UpdateDwsBillingStatementCopayCoordinationStatusRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingStatementCopayCoordinationStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'copayCoordinationStatus'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'copayCoordinationStatus'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode(['response' => 'data']), $response->getContent());
        });
        $this->should('update Entity using use case', function (): void {
            $this->updateDwsBillingStatementCopayCoordinationStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    $this->examples->dwsBillingStatements[0]->id,
                    equalTo(DwsBillingStatementCopayCoordinationStatus::unclaimable())
                )
                ->andReturn(['response' => 'data']);
            app()->call(
                [$this->controller, 'copayCoordinationStatus'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
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
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}',
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
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'billingId' => $this->examples->dwsBillings[0]->id,
                    'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->getInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $billingId = $this->examples->dwsBillings[0]->id;
            $billingBundleId = $this->examples->dwsBillingBundles[1]->id;
            $statementId = $this->examples->dwsBillingStatements[2]->id;
            $this->getDwsBillingStatementInfoUseCase
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
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}/status',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateDwsBillingStatementStatusRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingStatementStatusRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode(['response' => 'data']), $response->getContent());
        });
        $this->should('use UpdateDwsBillingStatementStatusUseCase', function (): void {
            $this->editDwsBillingStatementStatusUseCase
                ->expects('handle')
                ->andReturnUsing(function (
                    Context $context,
                    int $billingId,
                    int $bundleId,
                    int $id,
                    DwsBillingStatus $status
                ): array {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($this->examples->dwsBillingStatements[0]->dwsBillingId, $billingId);
                    $this->assertSame($this->examples->dwsBillingStatements[0]->dwsBillingBundleId, $bundleId);
                    $this->assertSame($this->examples->dwsBillingStatements[0]->id, $id);
                    $this->assertEquals($this->payload(), $status);

                    return ['response' => 'data'];
                });
            app()->call(
                [$this->controller, 'status'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
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
            '/api/dws-billings/{billingId}/bundles/{billingBundleId}/statements/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->updateInput())
        ));
        app()->bind(UpdateDwsBillingStatementRequest::class, function () {
            $request = Mockery::mock(UpdateDwsBillingStatementRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $response = app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
            );

            $this->assertSame(Json::encode(['response' => 'data']), $response->getContent());
        });
        $this->should('update DwsCertification using use case', function (): void {
            $this->updateDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    $this->examples->dwsBillingStatements[0]->id,
                    equalTo($this->updatePayload())
                )
                ->andReturn(['response' => 'data']);
            app()->call(
                [$this->controller, 'update'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
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
            '/api/dws-billings/{billingId}/statement-refresh',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
            ])
        ));
        app()->bind(RefreshDwsBillingStatementRequest::class, function () {
            $request = Mockery::mock(RefreshDwsBillingStatementRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn([
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
            ])->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call(
                    [$this->controller, 'refresh'],
                    [
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
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
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
                    ],
                )->getContent()
            );
        });
        $this->should('refresh dws statement using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(RefreshDwsBillingStatementJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call(
                [$this->controller, 'refresh'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'ids' => [$this->examples->dwsBillingStatements[0]->id],
                ],
            );
        });
    }

    /**
     * 状態編集用の入力を組み立てる.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'status' => $this->examples->dwsBillingStatements[0]->status->value(),
        ];
    }

    /**
     * 編集用の入力を組み立てる.
     *
     * @return array|array[][]
     */
    private function updateInput(): array
    {
        return [
            'aggregates' => [
                [
                    'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService()->value(),
                    'managedCopay' => 0,
                    'subtotalSubsidy' => 1000,
                ],
            ],
        ];
    }

    /**
     * 上限管理結果編集用の入力を組み立てる.
     *
     * @return array
     */
    private function copayCoordinationInput(): array
    {
        return [
            'result' => CopayCoordinationResult::appropriated()->value(),
            'amount' => 0,
        ];
    }

    /**
     * 状態編集リクエストクラスの戻り値を返す.
     *
     * @return DwsBillingStatus
     */
    private function payload(): DwsBillingStatus
    {
        return $this->examples->dwsBillingStatements[0]->status;
    }

    private function updatePayload(): array
    {
        return [
            [
                'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                'managedCopay' => 0,
                'subtotalSubsidy' => 1000,
            ],
        ];
    }

    /**
     * 上限管理結果変数リクエストクラスの戻り値を返す.
     *
     * @return array
     */
    private function copayCoordinationPayload(): array
    {
        return [
            'result' => CopayCoordinationResult::appropriated(),
            'amount' => 0,
        ];
    }
}
