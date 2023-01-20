<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsInsCardController;
use App\Http\Requests\CreateLtcsInsCardRequest;
use App\Http\Requests\DeleteLtcsInsCardRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsInsCardRequest;
use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Permission\Permission;
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
use Tests\Unit\Mixins\CreateLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\DeleteLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\EditLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * LtcsInsCardController のテスト.
 */
class LtcsInsCardControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateLtcsInsCardUseCaseMixin;
    use EditLtcsInsCardUseCaseMixin;
    use DeleteLtcsInsCardUseCaseMixin;
    use ExamplesConsumer;
    use LookupLtcsInsCardUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private LtcsInsCardController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsInsCardControllerTest $self): void {
            $self->createLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsInsCards[0])
                ->byDefault();
            $self->editLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsInsCards[0])
                ->byDefault();
            $self->deleteLtcsInsCardUseCase
                ->allows('handle')
                ->byDefault();
            $self->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsInsCards[0]))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(LtcsInsCardController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/ltcs-ins-cards',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateLtcsInsCardRequest::class, function () {
            $request = Mockery::mock(CreateLtcsInsCardRequest::class)->makePartial();
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
        $this->should('create LtcsInsCard using use case', function (): void {
            $this->createLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    equalTo(LtcsInsCard::create($this->payload()))
                )
                ->andReturn($this->examples->ltcsInsCards[0]);

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
            '/api/users/{userId}/ltcs-ins-cards/{id}',
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
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewLtcsInsCards(),
                    $this->examples->users[0]->id,
                    self::NOT_EXISTING_ID
                )->andReturn(Seq::emptySeq());
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
        $this->should('return a JSON of LtcsInsCard', function (): void {
            $ltcsInsCard = $this->examples->ltcsInsCards[0];
            $response = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $ltcsInsCard->id]
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('ltcsInsCard')), $response->getContent());
        });
        $this->should('get LtcsInsCard using use case', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewLtcsInsCards(),
                    $this->examples->users[0]->id,
                    $this->examples->ltcsInsCards[0]->id
                )
                ->andReturn(Seq::from($this->examples->ltcsInsCards[0]));
            app()->call(
                [$this->controller, 'get'],
                ['id' => $this->examples->users[0]->id, 'userId' => $this->examples->ltcsInsCards[0]->id]
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
            '/api/users/{userId}/ltcs-ins-cards/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateLtcsInsCardRequest::class, function () {
            $request = Mockery::mock(UpdateLtcsInsCardRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->ltcsInsCards[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $ltcsInsCard = $this->examples->ltcsInsCards[0];

            $response = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $ltcsInsCard->id]
            );

            $this->assertSame(Json::encode(compact('ltcsInsCard'), 0), $response->getContent());
        });
        $this->should('update LtcsInsCard using use case', function (): void {
            $payload = null;
            $this->editLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->ltcsInsCards[0]->id,
                    Mockery::capture($payload)
                )
                ->andReturn($this->examples->ltcsInsCards[0]);

            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->ltcsInsCards[0]->id]
            );

            $this->assertEquals($this->payload(), $payload);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/ltcs-ins-cards/{id}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode([]),
        ));
        app()->bind(DeleteLtcsInsCardRequest::class, function () {
            $request = Mockery::mock(DeleteLtcsInsCardRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call(
                    [$this->controller, 'delete'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->ltcsInsCards[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call(
                    [$this->controller, 'delete'],
                    ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->ltcsInsCards[0]->id]
                )->getContent()
            );
        });
        $this->should('delete LtcsInsCard using use case', function (): void {
            $this->deleteLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->ltcsInsCards[0]->id
                );
            app()->call(
                [$this->controller, 'delete'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->ltcsInsCards[0]->id]
            );
        });
    }

    /**
     * Input.
     *
     * @return array
     */
    private function input(): array
    {
        $values = [
            'status' => LtcsInsCardStatus::applied()->value(),
            'insNumber' => '0123456789',
            'insurerNumber' => '012345',
            'insurerName' => '新垣栄作',
            'ltcsLevel' => LtcsLevel::careLevel1()->value(),
            'copayRate' => 12345006789,
            'issuedOn' => '2015-01-01',
            'effectivatedOn' => '2016-01-01',
            'certificatedOn' => '2016-12-31',
            'activatedOn' => '2017-06-30',
            'deactivatedOn' => '2018-10-15',
            'copayActivatedOn' => '2018-12-31',
            'copayDeactivatedOn' => '2019-01-01',
            'careManagerName' => 'テスト太郎',
            'carePlanAuthorType' => LtcsCarePlanAuthorType::self()->value(),
            'communityGeneralSupportCenterId' => null,
            'carePlanAuthorOfficeId' => null,
        ];
        $maxBenefitQuotas = Seq::fromArray($this->examples->ltcsInsCards[0]->maxBenefitQuotas)
            ->map(fn (LtcsInsCardMaxBenefitQuota $maxBenefitQuota) => [
                'ltcsInsCardServiceType' => $maxBenefitQuota->ltcsInsCardServiceType->value(),
                'maxBenefitQuota' => $maxBenefitQuota->maxBenefitQuota,
            ]);

        return $values + ['maxBenefitQuotas' => $maxBenefitQuotas->toArray()];
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->input();
        $values = [
            'status' => LtcsInsCardStatus::from($input['status']),
            'ltcsLevel' => LtcsLevel::from($input['ltcsLevel']),
            'issuedOn' => Carbon::parse($input['issuedOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'certificatedOn' => Carbon::parse($input['certificatedOn']),
            'activatedOn' => Carbon::parse($input['activatedOn']),
            'deactivatedOn' => Carbon::parse($input['deactivatedOn']),
            'copayActivatedOn' => Carbon::parse($input['copayActivatedOn']),
            'copayDeactivatedOn' => Carbon::parse($input['copayDeactivatedOn']),
            'careManagerName' => $input['careManagerName'] ?? '',
            'carePlanAuthorType' => LtcsCarePlanAuthorType::from($input['carePlanAuthorType']),
            'communityGeneralSupportCenterId' => Seq::from(
                LtcsLevel::supportLevel1(),
                LtcsLevel::supportLevel2(),
                LtcsLevel::target()
            )->contains(LtcsLevel::from($input['ltcsLevel']))
                ? $input['communityGeneralSupportCenterId']
                : null,
            'carePlanAuthorOfficeId' => LtcsCarePlanAuthorType::from($input['carePlanAuthorType']) === LtcsCarePlanAuthorType::self()
                ? null
                : $input['carePlanAuthorOfficeId'],
            'isEnabled' => true,
        ];
        $maxBenefitQuotas = Seq::fromArray($input['maxBenefitQuotas'])
            ->map(fn ($x): LtcsInsCardMaxBenefitQuota => LtcsInsCardMaxBenefitQuota::create([
                'ltcsInsCardServiceType' => LtcsInsCardServiceType::from($x['ltcsInsCardServiceType']),
                'maxBenefitQuota' => $x['maxBenefitQuota'],
            ]));

        return $values + ['maxBenefitQuotas' => $maxBenefitQuotas->toArray()] + $input;
    }
}
