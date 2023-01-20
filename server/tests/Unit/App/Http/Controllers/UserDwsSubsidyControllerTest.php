<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserDwsSubsidyController;
use App\Http\Requests\CreateUserDwsSubsidyRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserDwsSubsidyRequest;
use Domain\Common\CarbonRange;
use Domain\Common\Rounding;
use Domain\Permission\Permission;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\EditUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LookupUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UserDwsSubsidyController のテスト.
 */
class UserDwsSubsidyControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateUserDwsSubsidyUseCaseMixin;
    use EditUserDwsSubsidyUseCaseMixin;
    use ExamplesConsumer;
    use LookupUserDwsSubsidyUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private UserDwsSubsidy $userDwsSubsidy;
    private UserDwsSubsidyController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserDwsSubsidyControllerTest $self): void {
            $self->userDwsSubsidy = $self->examples->userDwsSubsidies[0];
            $self->createUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn($self->examples->userDwsSubsidies[0])
                ->byDefault();
            $self->editUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn($self->examples->userDwsSubsidies[0])
                ->byDefault();
            $self->lookupUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userDwsSubsidies[0]))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(UserDwsSubsidyController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/dws-subsidies',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateUserDwsSubsidyRequest::class, function () {
            $request = Mockery::mock(CreateUserDwsSubsidyRequest::class)->makePartial();
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
        $this->should('create UserDwsSubsidy using use case', function (): void {
            $this->createUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->id, equalTo(UserDwsSubsidy::create($this->payload())))
                ->andReturn($this->examples->userDwsSubsidies[0]);

            app()->call([$this->controller, 'create'], ['userId' => $this->examples->users[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/dws-subsidies/{id}',
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
                    'userId' => $this->userDwsSubsidy->userId,
                    'id' => $this->userDwsSubsidy->id,
                ])->getStatusCode()
            );
        });
        $this->should('return a JSON of UserDwsSubsidy', function (): void {
            $dwsSubsidy = $this->examples->userDwsSubsidies[0];
            $response = app()->call([$this->controller, 'get'], [
                'userId' => $this->userDwsSubsidy->userId,
                'id' => $this->userDwsSubsidy->id,
            ]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('dwsSubsidy')), $response->getContent());
        });
        $this->should('get UserDwsSubsidy using use case', function (): void {
            $this->lookupUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserDwsSubsidies(), $this->userDwsSubsidy->userId, $this->userDwsSubsidy->id)
                ->andReturn(Seq::from($this->userDwsSubsidy));

            app()->call([$this->controller, 'get'], [
                'id' => $this->userDwsSubsidy->id,
                'userId' => $this->userDwsSubsidy->userId,
            ]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserDwsSubsidies(), $this->userDwsSubsidy->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], [
                        'id' => self::NOT_EXISTING_ID,
                        'userId' => $this->userDwsSubsidy->userId,
                    ]);
                }
            );
        });
        $this->should('throw a NotFoundException when the userId not have id`s entity', function (): void {
            $this->lookupUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserDwsSubsidies(), self::NOT_EXISTING_ID, $this->userDwsSubsidy->id)
                ->andThrow(NotFoundException::class);

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], [
                        'id' => $this->userDwsSubsidy->id,
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
    public function describe_update(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/dws-subsidies/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputUpdate())
        ));
        app()->bind(UpdateUserDwsSubsidyRequest::class, function () {
            $request = Mockery::mock(UpdateUserDwsSubsidyRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userDwsSubsidies[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $dwsSubsidy = $this->examples->userDwsSubsidies[0];
            $this->assertSame(
                Json::encode(compact('dwsSubsidy')),
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userDwsSubsidies[0]->id]
                )->getContent()
            );
        });
        $this->should('update UserDwsSubsidy using use case', function (): void {
            $this->editUserDwsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userDwsSubsidies[0]->id,
                    $this->payload()
                )
                ->andReturn($this->examples->userDwsSubsidies[0]);
            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->userDwsSubsidies[0]->id]
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
            'period' => [
                'start' => $this->userDwsSubsidy->period->start,
                'end' => $this->userDwsSubsidy->period->end,
            ],
            'cityName' => $this->userDwsSubsidy->cityName,
            'cityCode' => $this->userDwsSubsidy->cityCode,
            'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
            'factor' => UserDwsSubsidyFactor::copay()->value(),
            'benefitRate' => $this->userDwsSubsidy->benefitRate,
            'copayRate' => $this->userDwsSubsidy->copayRate,
            'rounding' => Rounding::floor()->value(),
            'benefitAmount' => $this->userDwsSubsidy->benefitAmount,
            'copayAmount' => $this->userDwsSubsidy->copayAmount,
            'note' => $this->userDwsSubsidy->note,
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function inputUpdate(): array
    {
        return $this->inputCreate();
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->inputCreate();
        return [
            'period' => CarbonRange::create($input['period']),
            'subsidyType' => UserDwsSubsidyType::from($input['subsidyType']),
            'factor' => UserDwsSubsidyFactor::from($input['factor']),
            'rounding' => Rounding::from($input['rounding']),
        ] + $input;
    }
}
