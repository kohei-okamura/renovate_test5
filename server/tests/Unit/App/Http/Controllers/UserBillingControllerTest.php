<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserBillingController;
use App\Http\Requests\CreateUserBillingInvoiceRequest;
use App\Http\Requests\CreateUserBillingNoticeRequest;
use App\Http\Requests\CreateUserBillingReceiptRequest;
use App\Http\Requests\CreateUserBillingStatementRequest;
use App\Http\Requests\DeleteUserBillingDepositRequest;
use App\Http\Requests\FindUserBillingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserBillingDepositRequest;
use App\Http\Requests\UpdateUserBillingRequest;
use App\Jobs\CreateUserBillingInvoiceJob;
use App\Jobs\CreateUserBillingNoticeJob;
use App\Jobs\CreateUserBillingReceiptJob;
use App\Jobs\CreateUserBillingStatementJob;
use App\Jobs\DeleteUserBillingDepositJob;
use App\Jobs\UpdateUserBillingDepositJob;
use Closure;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Job\Job;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingResult;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DownloadFileUseCaseMixin;
use Tests\Unit\Mixins\FindUserBillingUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateUserBillingUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\UserBillingController} のテスト.
 */
class UserBillingControllerTest extends Test
{
    use CarbonMixin;
    use CreateJobUseCaseMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DownloadFileUseCaseMixin;
    use UpdateUserBillingUseCaseMixin;
    use ExamplesConsumer;
    use FindUserBillingUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;
    use JobsDispatcherMixin;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private const DIR = 'artifacts';
    private const FILENAME = 'dummy.pdf';
    private $resource;

    private UserBilling $userBilling;
    private FinderResult $finderResult;
    private UserBillingController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserBillingControllerTest $self): void {
            $self->updateUserBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateUserBillings(),
                    $self->examples->userBillings[1]->id
                )
                ->andReturn(Seq::from($self->examples->userBillings[1]->copy([
                    'result' => UserBillingResult::pending(),
                    'transactedAt' => null,
                ])))
                ->byDefault();
            $self->findUserBillingUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userBillings, Pagination::create()))
                ->byDefault();
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->resource = tmpfile();
            $self->downloadFileUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->resource))
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->finderResult = FinderResult::from($self->examples->userBillings, Pagination::create());
            $self->controller = app(UserBillingController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billings',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindUserBillingRequest::class, function () {
            $request = Mockery::mock(FindUserBillingRequest::class)->makePartial();
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
        $this->should('find UserBillings using use case', function (): void {
            $this->findUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::listUserBillings(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billings',
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
                app()->call([$this->controller, 'get'], ['id' => $this->userBilling->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of UserBilling', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->userBilling->id]);
            $userBilling = $this->userBilling;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('userBilling')), $response->getContent());
        });
        $this->should('get UserBilling using use case', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), $this->userBilling->id)
                ->andReturn(Seq::from($this->userBilling));

            app()->call([$this->controller, 'get'], ['id' => $this->userBilling->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['id' => self::NOT_EXISTING_ID]);
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
        $bankAccount = [
            'bankName' => 'ユースタイル銀行',
            'bankCode' => '0123',
            'bankBranchName' => '中野ハーモニータワー支店',
            'bankBranchCode' => '456',
            'bankAccountType' => BankAccountType::ordinaryDeposit()->value(),
            'bankAccountNumber' => '0123456',
            'bankAccountHolder' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ-ABC().-/',
        ];

        $input = [
            'id' => $this->examples->userBillings[1]->id,
            'carriedOverAmount' => 1234,
            'paymentMethod' => PaymentMethod::transfer()->value(),
            'bankAccount' => $bankAccount,
        ];

        $payload = [
            'carriedOverAmount' => 1234,
            'paymentMethod' => PaymentMethod::transfer(),
            'bankAccount' => UserBillingBankAccount::create([
                ...$bankAccount,
                'bankAccountType' => BankAccountType::from($bankAccount['bankAccountType']),
            ]),
        ];

        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billings/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($input)
        ));
        app()->bind(UpdateUserBillingRequest::class, function () {
            $request = Mockery::mock(UpdateUserBillingRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['id' => $this->examples->userBillings[1]->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $userBilling = $this->userBilling;
            $this->assertSame(
                Json::encode(compact('userBilling')),
                app()->call(
                    [$this->controller, 'update'],
                    ['id' => $this->examples->userBillings[1]->id]
                )->getContent()
            );
        });
        $this->should('update UserBilling using use case', function () use ($payload): void {
            $this->updateUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->userBillings[1]->id,
                    $payload
                )
                ->andReturn($this->userBilling);
            app()->call(
                [$this->controller, 'update'],
                ['id' => $this->examples->userBillings[1]->id]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_updateDeposit(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billings/deposit',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForUpdateDeposit())
        ));
        app()->bind(UpdateUserBillingDepositRequest::class, function () {
            $request = Mockery::mock(UpdateUserBillingDepositRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('payload')->andReturn($this->payloadForUpdateDeposit())->byDefault();
            return $request;
        });

        $this->should('return a 202 response', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[4]))
                ->byDefault();
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'updateDeposit'], $this->inputForUpdateDeposit())->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[4]))
                ->byDefault();
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'updateDeposit'], $this->inputForUpdateDeposit())->getContent()
            );
        });
        $this->should('confirm Shift using use case', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[4]))
                ->byDefault();
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(UpdateUserBillingDepositJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'updateDeposit'], $this->inputForUpdateDeposit());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_deleteDeposit(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billings/deposit',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['ids' => [$this->examples->userBillings[0]->id]]),
        ));
        app()->bind(DeleteUserBillingDepositRequest::class, function () {
            $request = Mockery::mock(DeleteUserBillingDepositRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'deleteDeposit'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'deleteDeposit'])->getContent()
            );
        });
        $this->should('delete deposit using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(DeleteUserBillingDepositJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'deleteDeposit']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createInvoice(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billing-invoices',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->userBillings[0]->id],
                'issuedOn' => '2021-11-10T00:00:00Z',
            ]),
        ));
        app()->bind(CreateUserBillingInvoiceRequest::class, function () {
            $request = Mockery::mock(CreateUserBillingInvoiceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createInvoice'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createInvoice'])->getContent()
            );
        });
        $this->should('create user-billing-invoice using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateUserBillingInvoiceJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createInvoice']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createReceipt(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billing-receipts',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->userBillings[0]->id],
                'issuedOn' => '2021-11-10T00:00:00Z',
            ]),
        ));
        app()->bind(CreateUserBillingReceiptRequest::class, function () {
            $request = Mockery::mock(CreateUserBillingReceiptRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createReceipt'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createReceipt'])->getContent()
            );
        });
        $this->should('create user-billing-receipt using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateUserBillingReceiptJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createReceipt']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_createNotice(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billing-notices',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(
                [
                    'ids' => [$this->examples->userBillings[0]->id],
                    'issuedOn' => '2021-11-10T00:00:00Z', ]
            ),
        ));
        app()->bind(CreateUserBillingNoticeRequest::class, function () {
            $request = Mockery::mock(CreateUserBillingNoticeRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createNotice'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createNotice'])->getContent()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createNotice'])->getContent()
            );
        });
        $this->should('create user-billing-notice using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateUserBillingNoticeJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createNotice']);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_createStatement(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/user-billing-statements',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([
                'ids' => [$this->examples->userBillings[0]->id],
                'issuedOn' => '2021-11-10T00:00:00Z',
            ]),
        ));
        app()->bind(CreateUserBillingStatementRequest::class, function () {
            $request = Mockery::mock(CreateUserBillingStatementRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 202 response', function (): void {
            $this->assertSame(
                Response::HTTP_ACCEPTED,
                app()->call([$this->controller, 'createStatement'])->getStatusCode()
            );
        });
        $this->should('return a JSON of Domain\Job\Job', function (): void {
            $job = $this->examples->jobs[0];
            $this->assertSame(
                Json::encode(compact('job')),
                app()->call([$this->controller, 'createStatement'])->getContent()
            );
        });
        $this->should('create user-billing-statement using use case', function (): void {
            $this->createJobUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, Mockery::any())
                ->andReturnUsing(function (Context $context, Closure $f): Job {
                    // 引数が Domain\Job\Job かの検証
                    $f($this->examples->jobs[0]);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(CreateUserBillingStatementJob::class);
                    return $this->examples->jobs[0];
                });

            app()->call([$this->controller, 'createStatement']);
        });
    }

    /**
     * 入金日更新用Input.
     *
     * @return array
     */
    private function inputForUpdateDeposit(): array
    {
        return [
            'ids' => [$this->examples->userBillings[4]->id],
            'depositedOn' => Carbon::now()->toString(),
        ];
    }

    /**
     * 入金日更新用payload.
     *
     * @return array
     */
    private function payloadForUpdateDeposit(): array
    {
        return [
            'ids' => [$this->examples->userBillings[4]->id],
            'depositedAt' => Carbon::now(),
        ];
    }
}
