<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\OwnExpenseProgramController;
use App\Http\Requests\CreateOwnExpenseProgramRequest;
use App\Http\Requests\FindOwnExpenseProgramRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOwnExpenseProgramRequest;
use Domain\Common\Expense;
use Domain\Common\Pagination;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\FinderResult;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\EditOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\FindOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\OwnExpenseProgramController} のテスト.
 */
class OwnExpenseProgramControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateOwnExpenseProgramUseCaseMixin;
    use EditOwnExpenseProgramUseCaseMixin;
    use ExamplesConsumer;
    use FindOwnExpenseProgramUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private OwnExpenseProgram $ownExpenseProgram;
    private FinderResult $finderResult;
    private OwnExpenseProgramController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OwnExpenseProgramControllerTest $self): void {
            $self->createOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn($self->examples->ownExpensePrograms[0])
                ->byDefault();
            $self->editOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn($self->examples->ownExpensePrograms[0])
                ->byDefault();
            $self->findOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->ownExpensePrograms, Pagination::create()))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();

            $self->ownExpenseProgram = $self->examples->ownExpensePrograms[0];
            $self->finderResult = FinderResult::from($self->examples->ownExpensePrograms, Pagination::create());
            $self->controller = app(OwnExpenseProgramController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/own-expense-programs',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateOwnExpenseProgramRequest::class, function () {
            $request = Mockery::mock(CreateOwnExpenseProgramRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'])->getContent()
            );
        });
        $this->should('create OwnExpenseProgram using use case', function (): void {
            $this->createOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, equalTo(OwnExpenseProgram::create($this->payload())))
                ->andReturn($this->ownExpenseProgram);

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
            '/api/own-expense-programs/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->ownExpenseProgram->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of ownExpenseProgram', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->ownExpenseProgram->id]);
            $ownExpenseProgram = $this->ownExpenseProgram;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('ownExpenseProgram')), $response->getContent());
        });
        $this->should('get ownExpenseProgram using use case', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewOwnExpensePrograms(), $this->ownExpenseProgram->id)
                ->andReturn(Seq::from($this->ownExpenseProgram));

            app()->call([$this->controller, 'get'], ['id' => $this->ownExpenseProgram->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewOwnExpensePrograms(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

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
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/own-expense-programs',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindOwnExpenseProgramRequest::class, function () {
            $request = Mockery::mock(FindOwnExpenseProgramRequest::class)->makePartial();
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
        $this->should('find OwnExpensePrograms using use case', function (): void {
            $this->findOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, Permission::listOwnExpensePrograms(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
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
            '/api/own-expense-programs/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateOwnExpenseProgramRequest::class, function () {
            $request = Mockery::mock(UpdateOwnExpenseProgramRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['id' => $this->ownExpenseProgram->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $ownExpenseProgram = $this->ownExpenseProgram;
            $this->assertSame(
                Json::encode(compact('ownExpenseProgram')),
                app()->call(
                    [$this->controller, 'update'],
                    ['id' => $this->ownExpenseProgram->id]
                )->getContent()
            );
        });
        $this->should('update OwnExpenseProgram using use case', function (): void {
            $this->editOwnExpenseProgramUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->ownExpenseProgram->id,
                    $this->payloadForUpdate()
                )
                ->andReturn($this->ownExpenseProgram);
            app()->call(
                [$this->controller, 'update'],
                ['id' => $this->ownExpenseProgram->id]
            );
        });
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->input();
        return [
            'officeId' => $input['officeId'],
            'name' => $input['name'],
            'durationMinutes' => $input['durationMinutes'],
            'fee' => Expense::create([
                'taxExcluded' => $input['fee']['taxExcluded'],
                'taxIncluded' => $input['fee']['taxIncluded'],
                'taxType' => TaxType::from($input['fee']['taxType']),
                'taxCategory' => TaxCategory::from($input['fee']['taxCategory']),
            ]),
            'note' => $input['note'],
        ];
    }

    /**
     * 更新用 payload が返す配列を生成.
     *
     * @return array
     */
    private function payloadForUpdate(): array
    {
        $input = $this->input();
        return [
            'name' => $input['name'],
            'note' => $input['note'],
        ];
    }

    /**
     * 登録用input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'officeId' => $this->ownExpenseProgram->officeId,
            'name' => $this->ownExpenseProgram->name,
            'durationMinutes' => $this->ownExpenseProgram->durationMinutes,
            'fee' => [
                'taxExcluded' => $this->ownExpenseProgram->fee->taxExcluded,
                'taxIncluded' => $this->ownExpenseProgram->fee->taxIncluded,
                'taxType' => $this->ownExpenseProgram->fee->taxType->value(),
                'taxCategory' => $this->ownExpenseProgram->fee->taxCategory->value(),
            ],
            'note' => $this->ownExpenseProgram->note,
        ];
    }
}
