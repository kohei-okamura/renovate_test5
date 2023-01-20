<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\OfficeGroupController;
use App\Http\Requests\BulkUpdateOfficeGroupRequest;
use App\Http\Requests\CreateOfficeGroupRequest;
use App\Http\Requests\DeleteOfficeGroupRequest;
use App\Http\Requests\FindOfficeGroupRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOfficeGroupRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\OfficeGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BulkEditOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\DeleteOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\EditOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * OfficeGroupController のテスト.
 */
class OfficeGroupControllerTest extends Test
{
    use BulkEditOfficeGroupUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use CreateOfficeGroupUseCaseMixin;
    use DeleteOfficeGroupUseCaseMixin;
    use EditOfficeGroupUseCaseMixin;
    use ExamplesConsumer;
    use FindOfficeGroupUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private OfficeGroupController $controller;
    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeGroupControllerTest $self): void {
            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->officeGroups, $pagination);
            $self->createOfficeGroupUseCase->allows('handle')->andReturn($self->examples->officeGroups[0])->byDefault();
            $self->deleteOfficeGroupUseCase->allows('handle')->byDefault();
            $self->editOfficeGroupUseCase->allows('handle')->andReturn($self->finderResult)->byDefault();
            $self->bulkEditOfficeGroupUseCase->allows('handle')->byDefault();
            $self->findOfficeGroupUseCase->allows('handle')->andReturn($self->finderResult)->byDefault();
            $self->lookupOfficeGroupUseCase->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));
            $self->controller = app(OfficeGroupController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateOfficeGroupRequest::class, function () {
            $request = Mockery::mock(CreateOfficeGroupRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
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
        $this->should('create OfficeGroup using use case', function (): void {
            $this->createOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->officeGroup()))
                ->andReturn($this->examples->officeGroups[0]);

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->examples->officeGroups[0]->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of OfficeGroup', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->examples->officeGroups[0]->id]);
            $officeGroup = $this->examples->officeGroups[0];

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('officeGroup')), $response->getContent());
        });
        $this->should('get OfficeGroup using use case', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($this->examples->officeGroups[0]));

            app()->call([$this->controller, 'get'], ['id' => $this->examples->officeGroups[0]->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
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
    public function describe_update(): void
    {
        app()->bind('request', fn (): Request => Request::create(
            '/api/office-groups/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->updateInput())
        ));
        app()->bind(UpdateOfficeGroupRequest::class, function () {
            $request = Mockery::mock(UpdateOfficeGroupRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->examples->officeGroups[0]->id])
                    ->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'update'], ['id' => $this->examples->officeGroups[0]->id])->getContent()
            );
        });
        $this->should('update OfficeGroup using use case', function (): void {
            $this->editOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->officeGroups[0]->id, $this->updateInput())
                ->andReturn($this->finderResult);

            app()->call([$this->controller, 'update'], ['id' => $this->examples->officeGroups[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_bulkUpdate(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->bulkUpdateInput())
        ));
        app()->bind(BulkUpdateOfficeGroupRequest::class, function () {
            $request = Mockery::mock(BulkUpdateOfficeGroupRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'bulkUpdate'])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'bulkUpdate'])->getContent()
            );
        });
        $this->should('bulk update OfficeGroup using use case', function (): void {
            $this->bulkEditOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->bulkUpdateInput()['list']))
                ->andReturn($this->examples->officeGroups[0]);

            app()->call([$this->controller, 'bulkUpdate']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindOfficeGroupRequest::class, function () {
            $request = Mockery::mock(FindOfficeGroupRequest::class)->makePartial();
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
        $this->should('find OfficeGroups using use case', function (): void {
            $this->findOfficeGroupUseCase
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
    public function describe_delete(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/office-groups/{id}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(DeleteOfficeGroupRequest::class, function () {
            $request = Mockery::mock(DeleteOfficeGroupRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'delete'], ['id' => $this->examples->officeGroups[0]->id])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'delete'], ['id' => $this->examples->officeGroups[0]->id])->getContent()
            );
        });
        $this->should('delete OfficeGroup using use case', function (): void {
            $this->deleteOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->officeGroups[0]->id)
                ->andReturnNull();

            app()->call([$this->controller, 'delete'], ['id' => $this->examples->officeGroups[0]->id]);
        });
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
            'name' => '北海道ブロック',
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function updateInput(): array
    {
        return [
            'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
            'name' => '北海道ブロック',
        ];
    }

    /**
     * 一括更新用Input.
     *
     * @return array
     */
    private function bulkUpdateInput(): array
    {
        return ['list' => [
            [
                'id' => $this->examples->officeGroups[0]->id,
                'parentOfficeGroupId' => null,
                'sortOrder' => 1111111111,
            ],
            [
                'id' => $this->examples->officeGroups[1]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
                'sortOrder' => 2222222222,
            ],
            [
                'id' => $this->examples->officeGroups[2]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
                'sortOrder' => 3333333333,
            ],
        ],
        ];
    }

    /**
     * 登録リクエストから生成されるはずの事業所グループ.
     *
     * @return \Domain\Office\OfficeGroup
     */
    private function officeGroup(): OfficeGroup
    {
        $input = $this->input();
        $overwrites = [
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return OfficeGroup::create($overwrites + $input);
    }
}
