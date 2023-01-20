<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\StaffPasswordResetController;
use App\Http\Requests\CreateStaffPasswordResetRequest;
use App\Http\Requests\StaffPasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateStaffPasswordResetUseCaseMixin;
use Tests\Unit\Mixins\GetStaffPasswordResetUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\ResetStaffPasswordUseCaseMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Test;

/**
 * StaffPasswordResetController のテスト.
 */
class StaffPasswordResetControllerTest extends Test
{
    use ContextMixin;
    use CreateStaffPasswordResetUseCaseMixin;
    use ExamplesConsumer;
    use GetStaffPasswordResetUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use ResetStaffPasswordUseCaseMixin;
    use RoleRepositoryMixin;
    use UnitSupport;

    private const CREATE_STAFF_PASSWORD_RESET_INPUT = [
        'email' => 'sample@example.com',
    ];
    private const STAFF_PASSWORD_RESET_INPUT = [
        'password' => 'abcdefgh',
    ];

    private StaffPasswordResetController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffPasswordResetControllerTest $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->getStaffPasswordResetUseCase
                ->allows('handle')
                ->andReturn($self->examples->staffPasswordResets[0])
                ->byDefault();

            $self->createStaffPasswordResetUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->staffPasswordResets[0]))
                ->byDefault();

            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->resetStaffPasswordUseCase->allows('handle')->byDefault();

            $self->controller = app(StaffPasswordResetController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/password-resets/TOKEN',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        $this->should('get the StaffPasswordReset', function (): void {
            $this->getStaffPasswordResetUseCase
                ->expects('handle')
                ->with($this->context, 'TOKEN')
                ->andReturn($this->examples->staffPasswordResets[0]);

            app()->call([$this->controller, 'get'], ['token' => 'TOKEN']);
        });
        $this->should('return a JSON of StaffPasswordReset', function (): void {
            $response = app()->call([$this->controller, 'get'], ['token' => 'TOKEN']);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame($this->examples->staffPasswordResets[0]->toJson(), $response->getContent());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/password-resets',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::CREATE_STAFF_PASSWORD_RESET_INPUT)
        ));
        app()->bind(CreateStaffPasswordResetRequest::class, function () {
            $request = Mockery::mock(CreateStaffPasswordResetRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('create StaffPasswordReset', function (): void {
            $this->createStaffPasswordResetUseCase
                ->expects('handle')
                ->with($this->context, self::CREATE_STAFF_PASSWORD_RESET_INPUT['email'])
                ->andReturn(Option::from($this->examples->staffPasswordResets[0]));

            app()->call([$this->controller, 'create']);
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
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/password-resets/TOKEN',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::STAFF_PASSWORD_RESET_INPUT)
        ));
        app()->bind(StaffPasswordResetRequest::class, function () {
            $request = Mockery::mock(StaffPasswordResetRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('update Staff', function (): void {
            $this->resetStaffPasswordUseCase
                ->expects('handle')
                ->with($this->context, 'TOKEN', self::STAFF_PASSWORD_RESET_INPUT['password'])
                ->andReturn(Option::from($this->examples->staffPasswordResets[0]));

            app()->call([$this->controller, 'update'], ['token' => 'TOKEN']);
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'update'], ['token' => 'TOKEN'])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'update'], ['token' => 'TOKEN'])->getContent()
            );
        });
    }
}
