<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\CopayListController;
use App\Http\Requests\CreateCopayListRequest;
use App\Jobs\CreateCopayListJob;
use Closure;
use Domain\Context\Context;
use Domain\Job\Job;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DownloadFileUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\CopayListController} のテスト.
 */
class CopayListControllerTest extends Test
{
    use CarbonMixin;
    use CreateJobUseCaseMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DownloadFileUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use JobsDispatcherMixin;

    private const DIR = 'artifacts';
    private const FILENAME = 'dummy.pdf';
    private $resource;

    private CopayListController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CopayListControllerTest $self): void {
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->resource = tmpfile();
            $self->downloadFileUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->resource))
                ->byDefault();

            $self->controller = app(CopayListController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billings/{billingId}/copay-list',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->dwsBillingStatements[0]->id],
                'isDivided' => false,
            ])
        ));
        app()->bind(CreateCopayListRequest::class, function () {
            $request = Mockery::mock(CreateCopayListRequest::class)->makePartial();
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
                    [$this->controller, 'create'],
                    [
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
                        'isDivided' => false,
                    ],
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call(
                    [$this->controller, 'create'],
                    [
                        'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                        'ids' => [$this->examples->dwsBillingStatements[0]->id],
                        'isDivided' => false,
                    ],
                )->getContent()
            );
        });
        $this->should('create copay-list using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateCopayListJob::class);
                    return $this->examples->jobs[0];
                });
            app()->call(
                [$this->controller, 'create'],
                [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'ids' => [$this->examples->dwsBillingStatements[0]->id],
                    'isDivided' => false,
                ],
            );
        });
        // TODO 本実装時にケースを追加する
    }
}
