<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\RoleController;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\DeleteRoleRequest;
use App\Http\Requests\FindRoleRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateRoleRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Domain\Role\RoleScope;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateRoleUseCaseMixin;
use Tests\Unit\Mixins\DeleteRoleUseCaseMixin;
use Tests\Unit\Mixins\EditRoleUseCaseMixin;
use Tests\Unit\Mixins\FindRoleUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * RoleController のテスト.
 */
class RoleControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateRoleUseCaseMixin;
    use DeleteRoleUseCaseMixin;
    use EditRoleUseCaseMixin;
    use ExamplesConsumer;
    use FindRoleUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use RoleRepositoryMixin;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private FinderResult $finderResult;
    private RoleController $controller;
    private Role $role;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RoleControllerTest $self): void {
            $self->role = $self->examples->roles[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);

            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->roles, $pagination);
            $self->findRoleUseCase->allows('handle')->andReturn($self->finderResult)->byDefault();

            $self->createRoleUseCase
                ->allows('handle')
                ->andReturn($self->examples->roles[0])
                ->byDefault();

            $self->deleteRoleUseCase
                ->allows('handle')
                ->byDefault();

            $self->editRoleUseCase
                ->allows('handle')
                ->andReturn($self->role)
                ->byDefault();

            $self->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->staffs[0]));

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->context->allows('isAuthorizedTo')->andReturn(true)->byDefault();

            $self->controller = app(RoleController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            'api/roles',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateRoleRequest::class, function () {
            $request = Mockery::mock(CreateRoleRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return the response that has http status created', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'])->getStatusCode()
            );
        });
        $this->should('return the response that content is empty', function (): void {
            $response = app()->call([$this->controller, 'create'])->getContent();
            $this->assertEmpty(json_decode($response, true));
        });
        $this->should('create role by useCase', function (): void {
            $this->createRoleUseCase
                ->expects('handle')
                ->withArgs(function (Context $context, Role $role) {
                    $this->assertModelStrictEquals($this->role(), $role);
                    return $context === $this->context;
                })
                ->andReturn($this->examples->roles[0]);

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
            '/api/roles/1',
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
        $this->should('get the Role', function (): void {
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->roles[0]->id)
                ->andReturn(Seq::from($this->examples->roles[0]));

            app()->call([$this->controller, 'get'], ['id' => $this->examples->roles[0]->id]);
        });
        $this->should('return a JSON of Role', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->examples->roles[0]->id]);
            $role = $this->examples->roles[0];

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('role')), $response->getContent());
        });
        $this->should('return a 404 response when Role not found', function (): void {
            $this->lookupRoleUseCase->allows('handle')->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function () {
                    app()->call([$this->controller, 'get'], ['id' => $this->examples->roles[0]->id]);
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
        app()->bind('request', fn () => Request::create(
            '/api/roles/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForUpdate())
        ));
        app()->bind(UpdateRoleRequest::class, function () {
            $request = Mockery::mock(UpdateRoleRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });

        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->role->id])->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $this->assertSame(
                Json::encode(['role' => $this->role], 0),
                app()->call([$this->controller, 'update'], ['id' => $this->role->id])->getContent()
            );
        });
        $this->should('update Role using use case', function (): void {
            $payload = [
                'permissions' => [
                    Permission::createStaffs(),
                ],
                'scope' => RoleScope::whole(),
            ] + $this->inputForUpdate();
            $this->editRoleUseCase
                ->expects('handle')
                ->with($this->context, $this->role->id, equalTo($payload))
                ->andReturn($this->role);

            app()->call([$this->controller, 'update'], ['id' => $this->role->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => Request::create(
            '/roles',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindRoleRequest::class, function () {
            $request = Mockery::mock(FindRoleRequest::class)->makePartial();
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
        $this->should('find Roles using use case', function (): void {
            $this->findRoleUseCase
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
            '/api/roles/{id}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(DeleteRoleRequest::class, function () {
            $request = Mockery::mock(DeleteRoleRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });

        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'delete'], ['id' => $this->role->id])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'delete'], ['id' => $this->role->id])->getContent()
            );
        });
        $this->should('delete Role using use case', function (): void {
            $this->deleteRoleUseCase
                ->expects('handle')
                ->with($this->context, $this->role->id);

            app()->call([$this->controller, 'delete'], ['id' => $this->role->id]);
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
            'name' => 'スタッフ登録',
            'isSystemAdmin' => false,
            'permissions' => [
                $this->examples->roles[0]->permissions[0]->value() => true,
            ],
            'scope' => RoleScope::whole(),
            'sortOrder' => 1,
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function inputForUpdate(): array
    {
        return [
            'name' => 'スタッフ更新',
            'isSystemAdmin' => false,
            'permissions' => [
                $this->examples->roles[0]->permissions[0]->value() => true,
            ],
            'scope' => RoleScope::whole()->value(),
        ];
    }

    /**
     * リクエストから生成されるはずのロール.
     *
     * @return \Domain\Role\Role
     */
    private function role(): Role
    {
        $input = $this->input();
        $input['permissions'] = Map::from($input['permissions'])
            ->filter(fn (bool $x, string $key): bool => $x)
            ->keys()
            ->map(fn (string $x): Permission => Permission::from($x))
            ->toArray();
        $overwrites = [
            'sortOrder' => Carbon::now()->timestamp,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return Role::create($overwrites + $input);
    }
}
