<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\InvitationController;
use App\Http\Requests\CreateInvitationRequest;
use App\Http\Requests\OrganizationRequest;
use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\ForbiddenException;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateInvitationUseCaseMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationByTokenUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\InvitationController} のテスト.
 */
class InvitationControllerTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use CreateInvitationUseCaseMixin;
    use ExamplesConsumer;
    use LookupInvitationByTokenUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupRoleUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private Invitation $invitation;
    private InvitationController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (InvitationControllerTest $self): void {
            $self->createInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();
            $self->lookupInvitationByTokenUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->invitations[0]))
                ->byDefault();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();
            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();

            $self->invitation = $self->examples->invitations[0];
            $self->controller = app(InvitationController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/invitations',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateInvitationRequest::class, function () {
            $request = Mockery::mock(CreateInvitationRequest::class)->makePartial();
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
        $this->should('create Invitation using use case', function (): void {
            $this->createInvitationUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->payload()))
                ->andReturn(Seq::from($this->invitation));

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/invitations/{token}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(OrganizationRequest::class, function () {
            $request = Mockery::mock(OrganizationRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'get'], ['token' => $this->invitation->token])->getStatusCode()
            );
        });
        $this->should('return a JSON of invitation', function (): void {
            $response = app()->call([$this->controller, 'get'], ['token' => $this->invitation->token]);
            $invitation = $this->invitation;

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('invitation')), $response->getContent());
        });
        $this->should('get invitation using use case', function (): void {
            $this->lookupInvitationByTokenUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->token)
                ->andReturn(Option::from($this->invitation));

            app()->call([$this->controller, 'get'], ['token' => $this->invitation->token]);
        });
        $this->should('throw ForbiddenException when the invitation has been expired', function (): void {
            $this->lookupInvitationByTokenUseCase
                ->expects('handle')
                ->andReturn(Option::from($this->invitation->copy(['expiredAt' => Carbon::now()->subMinute()])));

            $this->assertThrows(ForbiddenException::class, function (): void {
                app()->call([$this->controller, 'get'], ['token' => $this->invitation->token]);
            });
        });
        $this->should(
            'throw NotFoundException when LookupInvitationByTokenUseCase return none',
            function (): void {
                $this->lookupInvitationByTokenUseCase
                    ->expects('handle')
                    ->andReturn(Option::none());

                $this->assertThrows(NotFoundException::class, function (): void {
                    app()->call([$this->controller, 'get'], ['token' => $this->invitation->token]);
                });
            }
        );
    }

    /**
     * payload が返すドメインモデルを生成.
     *
     * @return \Domain\Staff\Invitation[]&\ScalikePHP\Seq
     */
    private function payload(): Seq
    {
        $email = $this->input()['emails'][0];
        return Seq::from(Invitation::create([...$this->input(), 'email' => $email]))->computed();
    }

    /**
     * 登録用input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'emails' => [$this->invitation->email],
            'officeIds' => $this->invitation->officeIds,
            'officeGroupIds' => $this->invitation->officeGroupIds,
            'roleIds' => $this->invitation->roleIds,
        ];
    }
}
