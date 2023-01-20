<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserDwsCalcSpecController;
use App\Http\Requests\CreateUserDwsCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserDwsCalcSpecRequest;
use Domain\Permission\Permission;
use Domain\User\UserDwsCalcSpec;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\EditUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\UserDwsCalcSpecController} のテスト.
 */
final class UserDwsCalcSpecControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateUserDwsCalcSpecUseCaseMixin;
    use LookupUserDwsCalcSpecUseCaseMixin;
    use EditUserDwsCalcSpecUseCaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use RoleRepositoryMixin;
    use RequestMixin;
    use UnitSupport;

    private UserDwsCalcSpec $userDwsCalcSpec;
    private UserDwsCalcSpecController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->userDwsCalcSpec = $self->examples->userDwsCalcSpecs[0];

            $self->createUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturns($self->userDwsCalcSpec)
                ->byDefault();

            $self->lookupUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->userDwsCalcSpec))
                ->byDefault();

            $self->editUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturns($self->userDwsCalcSpec)
                ->byDefault();

            $self->controller = app(UserDwsCalcSpecController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return a created entity when the use case is succeeded', function (): void {
            app()->bind('request', fn () => LumenRequest::create(
                '/api/users/{userId}/dws-calc-specs',
                'POST',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->inputForCreate())
            ));
            app()->bind(CreateUserDwsCalcSpecRequest::class, function () {
                $request = Mockery::mock(CreateUserDwsCalcSpecRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            });
            $this->should('return a 201 response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userDwsCalcSpecs[0]->userId]
                );

                $this->assertSame(Response::HTTP_CREATED, $actual->getStatusCode());
            });
            $this->should('return an empty response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userDwsCalcSpecs[0]->userId]
                );

                $this->assertSame('', $actual->getContent());
            });
            $this->should('create UserDwsCalcSpec using use case', function (): void {
                $this->createUserDwsCalcSpecUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->examples->userDwsCalcSpecs[0]->userId,
                        Mockery::capture($actual)
                    )
                    ->andReturn($this->userDwsCalcSpec);

                app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userDwsCalcSpecs[0]->userId]
                );
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/dws-calc-specs/{id}',
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
        $this->should('throw NotFoundException when id is not exists', function (): void {
            $this->lookupUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserDwsCalcSpecs(),
                    $this->examples->users[0]->id,
                    self::NOT_EXISTING_ID
                )->andReturn(Seq::empty());
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call(
                        [$this->controller, 'get'],
                        ['userId' => $this->examples->users[0]->id, 'id' => self::NOT_EXISTING_ID],
                    );
                }
            );
        });
        $this->should('return a JSON of UserDwsCalcSpec', function (): void {
            $dwsCalcSpec = $this->examples->userDwsCalcSpecs[0];
            $response = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $dwsCalcSpec->id]
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('dwsCalcSpec')), $response->getContent());
        });
        $this->should('get UserDwsCalcSpec using use case', function (): void {
            $this->lookupUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserDwsCalcSpecs(),
                    $this->examples->users[0]->id,
                    $this->examples->userDwsCalcSpecs[0]->id
                )
                ->andReturn(Seq::from($this->examples->userDwsCalcSpecs[0]));
            app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userDwsCalcSpecs[0]->id]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        $this->should('return a updated entity when the use case is succeeded', function (): void {
            app()->bind('request', fn () => LumenRequest::create(
                '/api/users/{userId}/dws-calc-specs/{id}',
                'PUT',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->input())
            ));
            app()->bind(UpdateUserDwsCalcSpecRequest::class, function () {
                $request = Mockery::mock(UpdateUserDwsCalcSpecRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            });
            $this->should('return a 200 response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'update'],
                    [
                        'userId' => $this->examples->userDwsCalcSpecs[0]->userId,
                        'id' => $this->examples->userDwsCalcSpecs[0]->id,
                    ]
                );

                $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
            });
            $this->should('return a response of entity', function (): void {
                $dwsCalcSpec = $this->examples->userDwsCalcSpecs[0];
                $response = app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $dwsCalcSpec->id]
                );

                $this->assertSame(Json::encode(compact('dwsCalcSpec'), 0), $response->getContent());
            });
            $this->should('update UserDwsCalcSpec using use case', function (): void {
                $this->editUserDwsCalcSpecUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->examples->userDwsCalcSpecs[0]->userId,
                        $this->examples->userDwsCalcSpecs[0]->id,
                        $this->payload()
                    )
                    ->andReturn($this->userDwsCalcSpec);

                app()->call(
                    [$this->controller, 'update'],
                    [
                        'userId' => $this->examples->userDwsCalcSpecs[0]->userId,
                        'id' => $this->examples->userDwsCalcSpecs[0]->id,
                    ]
                );
            });
        });
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function inputForCreate(): array
    {
        return [
            'effectivatedOn' => $this->userDwsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $this->userDwsCalcSpec->locationAddition->value(),

            // URLパラメータがMockで取れないのでここに追加
            'userId' => $this->examples->userDwsCalcSpecs[0]->userId,
        ];
    }

    /**
     * Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'effectivatedOn' => $this->userDwsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $this->userDwsCalcSpec->locationAddition->value(),
        ];
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'effectivatedOn' => $this->userDwsCalcSpec->effectivatedOn,
            'locationAddition' => $this->userDwsCalcSpec->locationAddition,
        ];
    }
}
