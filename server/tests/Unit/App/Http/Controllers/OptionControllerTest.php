<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\OptionController;
use App\Http\Requests\GetIndexOfficeGroupOptionRequest;
use App\Http\Requests\GetIndexOfficeOptionRequest;
use App\Http\Requests\GetIndexRoleOptionRequest;
use App\Http\Requests\GetIndexStaffOptionRequest;
use App\Http\Requests\GetIndexUserOptionRequest;
use Domain\Context\Context;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetIndexOfficeGroupOptionUseCaseMixin;
use Tests\Unit\Mixins\GetIndexOfficeOptionUseCaseMixin;
use Tests\Unit\Mixins\GetIndexRoleOptionUseCaseMixin;
use Tests\Unit\Mixins\GetIndexStaffOptionUseCaseMixin;
use Tests\Unit\Mixins\GetIndexUserOptionUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\OptionController} のテスト
 */
final class OptionControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetIndexOfficeOptionUseCaseMixin;
    use GetIndexOfficeGroupOptionUseCaseMixin;
    use GetIndexRoleOptionUseCaseMixin;
    use GetIndexStaffOptionUseCaseMixin;
    use GetIndexUserOptionUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private OptionController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->context
                ->allows('isAuthorizedTo')
                ->andReturn(true)
                ->byDefault();

            $self->getIndexOfficeOptionUseCase
                ->allows('handle')
                ->andReturn(Seq::from([
                    'text' => $self->examples->offices[0]->name,
                    'value' => $self->examples->offices[0]->id,
                    'keyword' => 'だるまさんがすっころんだ',
                ]))
                ->byDefault();

            $self->getIndexOfficeGroupOptionUseCase
                ->allows('handle')
                ->andReturn(Seq::from([
                    'text' => $self->examples->officeGroups[0]->name,
                    'value' => $self->examples->officeGroups[0]->id,
                ]))
                ->byDefault();

            $self->getIndexRoleOptionUseCase
                ->allows('handle')
                ->andReturn(Seq::from([
                    'text' => $self->examples->roles[0]->name,
                    'value' => $self->examples->roles[0]->id,
                ]))
                ->byDefault();
            $self->getIndexStaffOptionUseCase
                ->allows('handle')
                ->andReturn(Seq::from([
                    'text' => $self->examples->staffs[0]->name->displayName,
                    'value' => $self->examples->staffs[0]->id,
                ]))
                ->byDefault();
            $self->getIndexUserOptionUseCase
                ->allows('handle')
                ->andReturn(Seq::from([
                    'text' => $self->examples->users[0]->name->displayName,
                    'value' => $self->examples->users[0]->id,
                ]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();

            $self->controller = app(OptionController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_offices(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/options/offices',
            'GET',
            [
                'permission' => '' . Permission::listInternalOffices()->value(),
                'userId' => '' . $this->examples->users[0]->id,
                'purpose' => '' . Purpose::internal()->value(),
                'qualifications' => [OfficeQualification::dwsVisitingCareForPwsd()->value()],
                'isCommunityGeneralSupportCenter' => true,
            ]
        ));
        app()->bind(GetIndexOfficeOptionRequest::class, function () {
            $request = Mockery::mock(GetIndexOfficeOptionRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call([$this->controller, 'offices']);
            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an array of text and value', function (): void {
            $expected = Seq::from([
                'text' => $this->examples->offices[0]->name,
                'value' => $this->examples->offices[0]->id,
                'keyword' => 'だるまさんがすっころんだ',
            ]);

            $actual = app()->call([$this->controller, 'offices']);

            $this->assertSame(Json::encode($expected), $actual->getContent());
        });
        $this->should('use GetIndexOfficeOptionUseCase', function (): void {
            $this->getIndexOfficeOptionUseCase
                ->expects('handle')
                ->withArgs(function (
                    Context $context,
                    Option $permission,
                    Option $userId,
                    Option $purpose,
                    Option $isCommunityGeneralSupportCenter,
                    Seq $qualifications
                ): bool {
                    return $context === $this->context
                        && $permission->exists(fn (Permission $x): bool => $x === Permission::listInternalOffices())
                        && $userId->exists(fn (int $x): bool => $x === $this->examples->users[0]->id)
                        && $purpose->exists(fn (Purpose $x): bool => $x === Purpose::internal())
                        && $isCommunityGeneralSupportCenter->exists(fn (bool $x): bool => $x === true)
                        && $qualifications->toArray() === [OfficeQualification::dwsVisitingCareForPwsd()];
                })
                ->andReturn(Seq::from([
                    'text' => $this->examples->offices[0]->name,
                    'value' => $this->examples->offices[0]->id,
                    'keyword' => 'だるまさんがすっころんだ',
                ]));

            app()->call([$this->controller, 'offices']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_officeGroups(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/options/office-groups',
            'GET',
            ['permission' => Permission::listOfficeGroups()->value()],
        ));
        app()->bind(GetIndexOfficeGroupOptionRequest::class, function () {
            $request = Mockery::mock(GetIndexOfficeGroupOptionRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call([$this->controller, 'officeGroups']);
            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an array of text and value', function (): void {
            $expected = Seq::from([
                'text' => $this->examples->officeGroups[0]->name,
                'value' => $this->examples->officeGroups[0]->id,
            ]);

            $actual = app()->call([$this->controller, 'officeGroups']);

            $this->assertSame(Json::encode($expected), $actual->getContent());
        });
        $this->should('use GetIndexOfficeGroupOptionUseCase', function (): void {
            $this->getIndexOfficeGroupOptionUseCase
                ->expects('handle')
                ->with($this->context, Permission::listOfficeGroups())
                ->andReturn(Seq::from([
                    'text' => $this->examples->officeGroups[0]->name,
                    'value' => $this->examples->officeGroups[0]->id,
                ]));

            app()->call([$this->controller, 'officeGroups']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_roles(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/options/roles',
            'GET',
            ['permission' => Permission::listRoles()->value()],
        ));
        app()->bind(GetIndexRoleOptionRequest::class, function () {
            $request = Mockery::mock(GetIndexRoleOptionRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call([$this->controller, 'roles']);
            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an array of text and value', function (): void {
            $expected = Seq::from([
                'text' => $this->examples->roles[0]->name,
                'value' => $this->examples->roles[0]->id,
            ]);

            $actual = app()->call([$this->controller, 'roles']);

            $this->assertSame(Json::encode($expected), $actual->getContent());
        });
        $this->should('use GetIndexRoleOptionUseCase', function (): void {
            $this->getIndexRoleOptionUseCase
                ->expects('handle')
                ->with($this->context, Permission::listRoles())
                ->andReturn(Seq::from([
                    'text' => $this->examples->roles[0]->name,
                    'value' => $this->examples->roles[0]->id,
                ]));

            app()->call([$this->controller, 'roles']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_staffs(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/options/staffs',
            'GET',
            [
                'permission' => Permission::listStaffs()->value(),
                'officeIds' => [$this->examples->offices[0]->id],
            ],
        ));
        app()->bind(GetIndexStaffOptionRequest::class, function () {
            $request = Mockery::mock(GetIndexStaffOptionRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call([$this->controller, 'staffs']);
            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an array of text and value', function (): void {
            $expected = Seq::from([
                'text' => $this->examples->staffs[0]->name->displayName,
                'value' => $this->examples->staffs[0]->id,
            ]);

            $actual = app()->call([$this->controller, 'staffs']);

            $this->assertSame(Json::encode($expected), $actual->getContent());
        });
        $this->should('use GetIndexStaffOptionUseCase', function (): void {
            $this->getIndexStaffOptionUseCase
                ->expects('handle')
                ->with($this->context, Permission::listStaffs(), [$this->examples->offices[0]->id])
                ->andReturn(Seq::from([
                    'text' => $this->examples->staffs[0]->name->displayName,
                    'value' => $this->examples->staffs[0]->id,
                ]));

            app()->call([$this->controller, 'staffs']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_users(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/options/users',
            'GET',
            [
                'permission' => Permission::listUsers()->value(),
                'officeIds' => [$this->examples->offices[0]->id],
            ],
        ));
        app()->bind(GetIndexUserOptionRequest::class, function () {
            $request = Mockery::mock(GetIndexUserOptionRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call([$this->controller, 'users']);
            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return an array of text and value', function (): void {
            $expected = Seq::from([
                'text' => $this->examples->users[0]->name->displayName,
                'value' => $this->examples->users[0]->id,
            ]);

            $actual = app()->call([$this->controller, 'users']);

            $this->assertSame(Json::encode($expected), $actual->getContent());
        });
        $this->should('use GetIndexUserOptionUseCase', function (): void {
            $this->getIndexUserOptionUseCase
                ->expects('handle')
                ->with($this->context, Permission::listUsers(), [$this->examples->offices[0]->id])
                ->andReturn(Seq::from([
                    'text' => $this->examples->users[0]->name->displayName,
                    'value' => $this->examples->users[0]->id,
                ]));

            app()->call([$this->controller, 'users']);
        });
    }
}
