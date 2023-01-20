<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\WithdrawalTransactionController;
use App\Http\Requests\CreateWithdrawalTransactionFileRequest;
use App\Http\Requests\CreateWithdrawalTransactionRequest;
use App\Http\Requests\FindWithdrawalTransactionRequest;
use App\Http\Requests\ImportWithdrawalTransactionFileRequest;
use App\Jobs\CreateWithdrawalTransactionFileJob;
use App\Jobs\CreateWithdrawalTransactionJob;
use App\Jobs\ImportWithdrawalTransactionFileJob;
use Closure;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalTransaction;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\CreateJobWithFileUseCaseMixin;
use Tests\Unit\Mixins\FindWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\WithdrawalTransactionController} のテスト.
 */
final class WithdrawalTransactionControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use CreateJobWithFileUseCaseMixin;
    use ExamplesConsumer;
    use FindWithdrawalTransactionUseCaseMixin;
    use LookupUserBillingUseCaseMixin;
    use LookupWithdrawalTransactionUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use JobsDispatcherMixin;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private WithdrawalTransaction $withdrawalTransaction;
    private FinderResult $finderResult;
    private WithdrawalTransactionController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->createJobWithFileUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->findWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->withdrawalTransactions, Pagination::create()))
                ->byDefault();
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->userBillings[0]->copy([
                        'user' => $self->examples->userBillings[0]->user->copy([
                            'billingDestination' => $self->examples->userBillings[0]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ]),
                    $self->examples->userBillings[1]->copy([
                        'user' => $self->examples->userBillings[1]->user->copy([
                            'billingDestination' => $self->examples->userBillings[1]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'result' => UserBillingResult::pending(),
                    ])
                ))
                ->byDefault();
            $self->lookupWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->withdrawalTransactions[0]))
                ->byDefault();

            $self->withdrawalTransaction = $self->examples->withdrawalTransactions[0];
            $self->finderResult = FinderResult::from($self->examples->withdrawalTransactions, Pagination::create());
            $self->controller = app(WithdrawalTransactionController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/withdrawal-transactions',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['userBillingIds' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id]]),
        ));
        app()->bind(CreateWithdrawalTransactionRequest::class, function () {
            $request = Mockery::mock(CreateWithdrawalTransactionRequest::class)->makePartial();
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
        $this->should('create WithdrawalTransaction using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateWithdrawalTransactionJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/withdrawal-transactions',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindWithdrawalTransactionRequest::class, function () {
            $request = Mockery::mock(FindWithdrawalTransactionRequest::class)->makePartial();
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
        $this->should('find WithdrawalTransactions using use case', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->with($this->context, Permission::listWithdrawalTransactions(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createFile(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/withdrawal-transaction-files',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['id' => $this->examples->withdrawalTransactions[0]->id])
        ));
        app()->bind(CreateWithdrawalTransactionFileRequest::class, function () {
            $request = Mockery::mock(CreateWithdrawalTransactionFileRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createFile'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createFile'])->getContent()
            );
        });
        $this->should('create file using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateWithdrawalTransactionFileJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createFile']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_import(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/withdrawal-transaction-imports',
            'POST',
            [],
            [],
            ['file' => UploadedFile::fake()->create('example.txt')],
            ['CONTENT_TYPE' => 'multipart/form-data'],
        ));
        app()->bind(ImportWithdrawalTransactionFileRequest::class, function () {
            $request = Mockery::mock(ImportWithdrawalTransactionFileRequest::class)->makePartial();
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
        $this->should('import withdrawal transaction file using use case', function (): void {
            $this->createJobWithFileUseCase
                ->expects('handle')
                // FileInputStream::fromFile() は、ランダムファイル名を生成するので、検証しない
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any(), Mockery::any())
                ->andReturnUsing(function (Context $context, FileInputStream $stream, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0], 'DummyPath');
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(ImportWithdrawalTransactionFileJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'import']);
        });
    }
}
