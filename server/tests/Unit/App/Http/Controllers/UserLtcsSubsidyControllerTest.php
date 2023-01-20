<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserLtcsSubsidyController;
use App\Http\Requests\CreateUserLtcsSubsidyRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserLtcsSubsidyRequest;
use Domain\Permission\Permission;
use Domain\User\UserLtcsSubsidy;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\DeleteUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\EditUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LookupUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UserLtcsSubsidyController のテスト.
 */
class UserLtcsSubsidyControllerTest extends Test
{
    use ContextMixin;
    use CreateUserLtcsSubsidyUseCaseMixin;
    use DeleteUserLtcsSubsidyUseCaseMixin;
    use EditUserLtcsSubsidyUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserLtcsSubsidyUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private UserLtcsSubsidy $userLtcsSubsidy;
    private UserLtcsSubsidyController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserLtcsSubsidyControllerTest $self): void {
            $self->userLtcsSubsidy = $self->examples->userLtcsSubsidies[0];
            $self->createUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn($self->examples->userLtcsSubsidies[0])
                ->byDefault();
            $self->deleteUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->editUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn($self->examples->userLtcsSubsidies[0])
                ->byDefault();
            $self->lookupUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userLtcsSubsidies[0]))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(UserLtcsSubsidyController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/ltcs-subsidies',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateUserLtcsSubsidyRequest::class, function () {
            $request = Mockery::mock(CreateUserLtcsSubsidyRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['userId' => $this->examples->users[0]->id])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'], ['userId' => $this->examples->users[0]->id])->getContent()
            );
        });
        $this->should('create Subsidy using use case', function (): void {
            $this->createUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->id, equalTo(UserLtcsSubsidy::create($this->inputCreate())))
                ->andReturn($this->examples->userLtcsSubsidies[0]);

            app()->call([$this->controller, 'create'], ['userId' => $this->examples->users[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/ltcs-subsidies/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputUpdate())
        ));
        app()->bind(UpdateUserLtcsSubsidyRequest::class, function () {
            $request = Mockery::mock(UpdateUserLtcsSubsidyRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userLtcsSubsidies[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
            $this->assertSame(
                Json::encode(['ltcsSubsidy' => $userLtcsSubsidy]),
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userLtcsSubsidies[0]->id]
                )->getContent()
            );
        });
        $this->should('update Subsidy using use case', function (): void {
            $this->editUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsSubsidies[0]->id,
                    $this->payload()
                )
                ->andReturn($this->examples->userLtcsSubsidies[0]);
            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userLtcsSubsidies[0]->id]
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
            '/api/users/{userId}/ltcs-subsidies/{id}',
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
                app()->call([$this->controller, 'get'], [
                    'userId' => $this->userLtcsSubsidy->userId,
                    'id' => $this->userLtcsSubsidy->id,
                ])->getStatusCode()
            );
        });
        $this->should('return a JSON of Subsidy', function (): void {
            $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
            $response = app()->call([$this->controller, 'get'], [
                'userId' => $this->userLtcsSubsidy->userId,
                'id' => $this->userLtcsSubsidy->id,
            ]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(['ltcsSubsidy' => $userLtcsSubsidy]), $response->getContent());
        });
        $this->should('get Subsidy using use case', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserLtcsSubsidies(), $this->userLtcsSubsidy->userId, $this->userLtcsSubsidy->id)
                ->andReturn(Seq::from($this->userLtcsSubsidy));

            app()->call([$this->controller, 'get'], [
                'id' => $this->userLtcsSubsidy->id,
                'userId' => $this->userLtcsSubsidy->userId,
            ]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserLtcsSubsidies(), $this->userLtcsSubsidy->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], [
                        'id' => self::NOT_EXISTING_ID,
                        'userId' => $this->userLtcsSubsidy->userId,
                    ]);
                }
            );
        });
        $this->should('throw a NotFoundException when the userId not have id`s entity', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserLtcsSubsidies(), self::NOT_EXISTING_ID, $this->userLtcsSubsidy->id)
                ->andThrow(NotFoundException::class);

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], [
                        'id' => $this->userLtcsSubsidy->id,
                        'userId' => self::NOT_EXISTING_ID,
                    ]);
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/users/{userId}/ltcs-subsidies/{id}',
            'DELETE',
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
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'delete'], [
                    'userId' => $this->userLtcsSubsidy->userId,
                    'id' => $this->userLtcsSubsidy->id,
                ])->getStatusCode()
            );
        });
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function inputCreate(): array
    {
        return [
            'period' => $this->examples->userLtcsSubsidies[0]->period,
            'defrayerCategory' => $this->examples->userLtcsSubsidies[0]->defrayerCategory,
            'defrayerNumber' => $this->examples->userLtcsSubsidies[0]->defrayerNumber,
            'recipientNumber' => $this->examples->userLtcsSubsidies[0]->recipientNumber,
            'benefitRate' => $this->examples->userLtcsSubsidies[0]->benefitRate,
            'copay' => $this->examples->userLtcsSubsidies[0]->copay,
            'isEnabled' => true,
        ];
    }

    /**
     * 更新用input.
     *
     * @return array
     */
    private function inputUpdate(): array
    {
        return [
            'period' => $this->examples->userLtcsSubsidies[0]->period,
            'defrayerCategory' => $this->examples->userLtcsSubsidies[0]->defrayerCategory,
            'defrayerNumber' => $this->examples->userLtcsSubsidies[0]->defrayerNumber,
            'recipientNumber' => $this->examples->userLtcsSubsidies[0]->recipientNumber,
            'benefitRate' => $this->examples->userLtcsSubsidies[0]->benefitRate,
            'copay' => $this->examples->userLtcsSubsidies[0]->copay,
            'isEnabled' => true,
        ];
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return $this->inputUpdate();
    }
}
