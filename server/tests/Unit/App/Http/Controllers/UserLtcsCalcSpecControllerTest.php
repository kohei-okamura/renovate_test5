<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserLtcsCalcSpecController;
use App\Http\Requests\CreateUserLtcsCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserLtcsCalcSpecRequest;
use Domain\Permission\Permission;
use Domain\User\UserLtcsCalcSpec;
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
use Tests\Unit\Mixins\CreateUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\EditUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\UserLtcsCalcSpecController} のテスト.
 */
final class UserLtcsCalcSpecControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateUserLtcsCalcSpecUseCaseMixin;
    use LookupUserLtcsCalcSpecUseCaseMixin;
    use EditUserLtcsCalcSpecUseCaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use RoleRepositoryMixin;
    use RequestMixin;
    use UnitSupport;

    private UserLtcsCalcSpec $userLtcsCalcSpec;
    private UserLtcsCalcSpecController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->userLtcsCalcSpec = $self->examples->userLtcsCalcSpecs[0];

            $self->createUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturns($self->userLtcsCalcSpec)
                ->byDefault();

            $self->lookupUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->userLtcsCalcSpec))
                ->byDefault();

            $self->editUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturns($self->userLtcsCalcSpec)
                ->byDefault();

            $self->controller = app(UserLtcsCalcSpecController::class);
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
                '/api/users/{userId}/ltcs-calc-specs',
                'POST',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->inputForCreate())
            ));
            app()->bind(CreateUserLtcsCalcSpecRequest::class, function () {
                $request = Mockery::mock(CreateUserLtcsCalcSpecRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            });
            $this->should('return a 201 response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userLtcsCalcSpecs[0]->userId]
                );

                $this->assertSame(Response::HTTP_CREATED, $actual->getStatusCode());
            });
            $this->should('return an empty response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userLtcsCalcSpecs[0]->userId]
                );

                $this->assertSame('', $actual->getContent());
            });
            $this->should('create UserLtcsCalcSpec using use case', function (): void {
                $this->createUserLtcsCalcSpecUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->examples->userLtcsCalcSpecs[0]->userId,
                        Mockery::capture($actual)
                    )
                    ->andReturn($this->userLtcsCalcSpec);

                app()->call(
                    [$this->controller, 'create'],
                    ['userId' => $this->examples->userLtcsCalcSpecs[0]->userId]
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
            '/api/users/{userId}/ltcs-calc-specs/{id}',
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
            $this->lookupUserLtcsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserLtcsCalcSpecs(),
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
        $this->should('return a JSON of UserLtcsCalcSpec', function (): void {
            $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];
            $response = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $ltcsCalcSpec->id]
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('ltcsCalcSpec')), $response->getContent());
        });
        $this->should('get UserLtcsCalcSpec using use case', function (): void {
            $this->lookupUserLtcsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserLtcsCalcSpecs(),
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsCalcSpecs[0]->id
                )
                ->andReturn(Seq::from($this->examples->userLtcsCalcSpecs[0]));
            app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userLtcsCalcSpecs[0]->id]
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
                '/api/users/{userId}/ltcs-calc-specs/{id}',
                'PUT',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->input())
            ));
            app()->bind(UpdateUserLtcsCalcSpecRequest::class, function () {
                $request = Mockery::mock(UpdateUserLtcsCalcSpecRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            });
            $this->should('return a 200 response', function (): void {
                $actual = app()->call(
                    [$this->controller, 'update'],
                    [
                        'userId' => $this->examples->userLtcsCalcSpecs[0]->userId,
                        'id' => $this->examples->userLtcsCalcSpecs[0]->id,
                    ]
                );

                $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
            });
            $this->should('return a response of entity', function (): void {
                $ltcsCalcSpec = $this->examples->userLtcsCalcSpecs[0];
                $response = app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $ltcsCalcSpec->id]
                );

                $this->assertSame(Json::encode(compact('ltcsCalcSpec'), 0), $response->getContent());
            });
            $this->should('update UserLtcsCalcSpec using use case', function (): void {
                $this->editUserLtcsCalcSpecUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->examples->userLtcsCalcSpecs[0]->userId,
                        $this->examples->userLtcsCalcSpecs[0]->id,
                        $this->payload()
                    )
                    ->andReturn($this->userLtcsCalcSpec);

                app()->call(
                    [$this->controller, 'update'],
                    [
                        'userId' => $this->examples->userLtcsCalcSpecs[0]->userId,
                        'id' => $this->examples->userLtcsCalcSpecs[0]->id,
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
            'effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $this->userLtcsCalcSpec->locationAddition->value(),

            // URLパラメータがMockで取れないのでここに追加
            'userId' => $this->examples->userLtcsCalcSpecs[0]->userId,
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
            'effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn->toDateString(),
            'locationAddition' => $this->userLtcsCalcSpec->locationAddition->value(),
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
            'effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn,
            'locationAddition' => $this->userLtcsCalcSpec->locationAddition,
        ];
    }
}
