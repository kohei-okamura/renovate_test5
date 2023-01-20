<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\OrganizationSettingController;
use App\Http\Requests\CreateOrganizationSettingRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOrganizationSettingRequest;
use Domain\Organization\OrganizationSetting;
use Domain\Permission\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateOrganizationSettingUseCaseMixin;
use Tests\Unit\Mixins\EditOrganizationSettingUseCaseMixin;
use Tests\Unit\Mixins\LookupOrganizationSettingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\OrganizationSettingController} のテスト.
 */
class OrganizationSettingControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateOrganizationSettingUseCaseMixin;
    use EditOrganizationSettingUseCaseMixin;
    use ExamplesConsumer;
    use LookupOrganizationSettingUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private OrganizationSetting $organizationSetting;
    private OrganizationSettingController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationSettingControllerTest $self): void {
            $self->organizationSetting = $self->examples->organizationSettings[0];
            $self->createOrganizationSettingUseCase
                ->allows('handle')
                ->andReturn($self->organizationSetting)
                ->byDefault();
            $self->lookupOrganizationSettingUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->organizationSetting))
                ->byDefault();
            $self->editOrganizationSettingUseCase
                ->allows('handle')
                ->andReturn($self->organizationSetting)
                ->byDefault();

            $self->controller = app(OrganizationSettingController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/settings',
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
                app()->call([$this->controller, 'get'], ['id' => $this->organizationSetting->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of OrganizationSetting', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->organizationSetting->id]);
            $organizationSetting = $this->organizationSetting;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('organizationSetting')), $response->getContent());
        });
        $this->should('get OrganizationSetting using use case', function (): void {
            $this->lookupOrganizationSettingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewOrganizationSettings())
                ->andReturn(Option::some($this->organizationSetting));

            app()->call([$this->controller, 'get'], ['id' => $this->organizationSetting->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupOrganizationSettingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewOrganizationSettings())
                ->andReturn(Option::none());

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
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            'api/settings',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateOrganizationSettingRequest::class, function () {
            $request = Mockery::mock(CreateOrganizationSettingRequest::class)->makePartial();
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
        $this->should('create OrganizationSetting using use case', function (): void {
            $this->createOrganizationSettingUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->payload()))
                ->andReturn($this->organizationSetting);

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/settings',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputUpdate())
        ));
        app()->bind(UpdateOrganizationSettingRequest::class, function () {
            $request = Mockery::mock(UpdateOrganizationSettingRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'])->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $organizationSetting = $this->organizationSetting;
            $this->assertSame(
                Json::encode(compact('organizationSetting')),
                app()->call([$this->controller, 'update'])->getContent()
            );
        });
        $this->should('update organizationSetting using use case', function (): void {
            $this->editOrganizationSettingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->inputUpdate()
                )
                ->andReturn($this->organizationSetting);
            app()->call([$this->controller, 'update']);
        });
    }

    /**
     * payload が返すドメインモデルを生成.
     *
     * @return \Domain\Organization\OrganizationSetting
     */
    private function payload(): OrganizationSetting
    {
        return OrganizationSetting::create($this->inputCreate());
    }

    /**
     * 登録用input.
     *
     * @return array
     */
    private function inputCreate(): array
    {
        return [
            'bankingClientCode' => $this->organizationSetting->bankingClientCode,
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
}
