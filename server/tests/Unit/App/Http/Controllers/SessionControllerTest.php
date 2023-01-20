<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\SessionController;
use App\Http\Requests\AuthenticateStaffRequest;
use App\Http\Requests\Request;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AuthenticateStaffUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetSessionInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Mixins\StaffLoggedOutUseCaseMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * SessionController のテスト.
 */
class SessionControllerTest extends Test
{
    use AuthenticateStaffUseCaseMixin;
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetSessionInfoUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use SessionMixin;
    use StaffLoggedOutUseCaseMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private const CREATE_SESSION_INPUT = [
        'email' => 'sample@example.com',
        'password' => 'PassWoRD',
        'rememberMe' => '1',
    ];
    private const HAS_REMEMBER_ME_COOKIE = 'hasRememberMe';

    private SessionController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SessionControllerTest $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();

            $self->authenticateStaffUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->staffAndPermissions()))
                ->byDefault();

            $self->getSessionInfoUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->staffAndPermissions()))
                ->byDefault();

            $self->staffLoggedOutUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->config->allows('get')->with('zinger.remember_token.cookie_name')->andReturn('rememberToken');

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->session->allows('get')->andReturn($self->examples->staffs[0]->id)->byDefault();
            $self->session->allows('remove')->andReturn($self->examples->staffs[0]->id)->byDefault();
            $self->session->allows('put')->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->controller = app(SessionController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/sessions',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::CREATE_SESSION_INPUT)
        ));
        app()->bind(AuthenticateStaffRequest::class, function () {
            $request = Mockery::mock(AuthenticateStaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('session')->andReturn($this->session)->byDefault();
            return $request;
        });
        $this->should('return a JSON response', function (): void {
            $response = app()->call([$this->controller, 'create']);

            $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
            $this->assertSame(Json::encode($this->staffAndPermissions()), $response->getContent());
        });
        $this->should('return a 400 response when failed to authenticate', function (): void {
            $this->authenticateStaffUseCase->expects('handle')->andReturn(Option::none());
            app()->call([$this->controller, 'create'])->getContent();
        });
        $this->should('authenticate the staff', function (): void {
            $this->authenticateStaffUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    self::CREATE_SESSION_INPUT['email'],
                    self::CREATE_SESSION_INPUT['password'],
                    true
                )
                ->andReturn(Option::from($this->staffAndPermissions()));

            app()->call([$this->controller, 'create']);
        });
        $this->should('put the session', function (): void {
            $this->session->expects('put')->with('staffId', $this->examples->staffs[0]->id);

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/sessions',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('session')->andReturn($this->session)->byDefault();
            $request->allows('hasCookie')->andReturn(false)->byDefault();
            return $request;
        });
        $this->should('return an empty 204 response', function (): void {
            $response = app()->call([$this->controller, 'delete']);

            $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
            $this->assertSame('', $response->getContent());
        });
        $this->should('remove the session', function (): void {
            $this->session
                ->expects('remove')
                ->with('staffId')
                ->andReturn($this->examples->staffs[0]->id);
            app()->call([$this->controller, 'delete']);
        });
        $this->should('use StaffLoggedOutUseCase', function (): void {
            $this->staffLoggedOutUseCase
                ->expects('handle')
                ->with($this->context, Option::none())
                ->andReturnNull();
            app()->call([$this->controller, 'delete']);
        });
        $this->should('not use StaffLoggedOutUseCase when staffId is null', function (): void {
            $this->session
                ->expects('remove')
                ->with('staffId')
                ->andReturn(null);
            $this->staffLoggedOutUseCase
                ->shouldNotHaveBeenCalled(['handle']);
            app()->call([$this->controller, 'delete']);
        });
        $this->should('log using info', function (): void {
            $context = [
                'organizationId' => $this->examples->organizations[0]->id,
                'staffId' => $this->examples->staffs[0]->id,
            ];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフがログアウトしました', ['staffId' => $this->examples->staffs[0]->id] + $context);
            app()->call([$this->controller, 'delete']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_deleteWithRememberToken(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/sessions',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('session')->andReturn($this->session)->byDefault();
            $request->allows('hasCookie')->andReturn(true)->byDefault();
            $request->allows('cookie')->andReturn(Json::encode(['id' => self::HAS_REMEMBER_ME_COOKIE]));
            return $request;
        });
        $this->should('use UseCase with rememberToken', function (): void {
            $this->staffLoggedOutUseCase
                ->expects('handle')
                ->with($this->context, equalTo(Option::from(self::HAS_REMEMBER_ME_COOKIE)))
                ->andReturnNull();
            app()->call([$this->controller, 'delete']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind(Request::class, function () {
            $request = Request::create(
                '/api/sessions/my',
                'GET',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode(self::CREATE_SESSION_INPUT)
            );
            $request->setLaravelSession($this->session);
            return $request;
        });
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'get'])->getStatusCode()
            );
        });
        $this->should('return a 404 response when failed to authenticate', function (): void {
            $this->getSessionInfoUseCase->expects('handle')->andReturn(Option::none());
            app()->call([$this->controller, 'get'])->getContent();
        });
    }

    /**
     * スタッフ情報と権限コード一覧の連想配列.
     *
     * @return array
     */
    private function staffAndPermissions(): array
    {
        return [
            'auth' => [
                'permissions' => ['staff.create', 'staff.update'],
                'staff' => $this->examples->staffs[0],
            ],
        ];
    }
}
