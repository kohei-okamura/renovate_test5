<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\HomeHelpServiceCalcSpecController;
use App\Http\Requests\CreateHomeHelpServiceCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateHomeHelpServiceCalcSpecRequest;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\EditHomeHelpServiceCalcSpecMixin;
use Tests\Unit\Mixins\LookupHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * HomeHelpServiceCalcSpecController のテスト.
 */
class HomeHelpServiceCalcSpecControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateHomeHelpServiceCalcSpecUseCaseMixin;
    use EditHomeHelpServiceCalcSpecMixin;
    use ExamplesConsumer;
    use LookupHomeHelpServiceCalcSpecUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec;
    private HomeHelpServiceCalcSpecController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HomeHelpServiceCalcSpecControllerTest $self): void {
            $self->homeHelpServiceCalcSpec = $self->examples->homeHelpServiceCalcSpecs[0];

            $self->createHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn($self->homeHelpServiceCalcSpec)
                ->byDefault();

            $self->editHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn($self->homeHelpServiceCalcSpec)
                ->byDefault();

            $self->lookupHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->homeHelpServiceCalcSpec))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(HomeHelpServiceCalcSpecController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/home-help-service-calc-specs',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateHomeHelpServiceCalcSpecRequest::class, function () {
            $request = Mockery::mock(CreateHomeHelpServiceCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId])->getContent()
            );
        });
        $this->should('create HomeHelpServiceCalcSpec using use case', function (): void {
            $this->createHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->homeHelpServiceCalcSpec->officeId, equalTo(HomeHelpServiceCalcSpec::create(
                    [
                        'officeId' => $this->homeHelpServiceCalcSpec->officeId,
                        'period' => $this->homeHelpServiceCalcSpec->period,
                        'specifiedOfficeAddition' => $this->homeHelpServiceCalcSpec->specifiedOfficeAddition,
                        'treatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->treatmentImprovementAddition,
                        'specifiedTreatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->specifiedTreatmentImprovementAddition,
                        'baseIncreaseSupportAddition' => $this->homeHelpServiceCalcSpec->baseIncreaseSupportAddition,
                    ]
                )))
                ->andReturn($this->homeHelpServiceCalcSpec);

            app()->call([$this->controller, 'create'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{office_id}/home-help-service-calc-specs/{id}',
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
                app()->call([$this->controller, 'get'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of HomeHelpServiceCalcSpec', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(
                Json::encode(['homeHelpServiceCalcSpec' => $this->homeHelpServiceCalcSpec], 0),
                $response->getContent()
            );
        });
        $this->should('get homeHelpServiceCalcSpec using use case', function (): void {
            $this->lookupHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec->id)
                ->andReturn(Seq::from($this->homeHelpServiceCalcSpec));

            app()->call([$this->controller, 'get'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->homeHelpServiceCalcSpec->officeId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => self::NOT_EXISTING_ID]);
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
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/home-help-service-calc-specs/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputUpdate())
        ));
        app()->bind(UpdateHomeHelpServiceCalcSpecRequest::class, function () {
            $request = Mockery::mock(UpdateHomeHelpServiceCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $this->assertSame(
                Json::encode(['homeHelpServiceCalcSpec' => $this->homeHelpServiceCalcSpec], 0),
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id]
                )->getContent()
            );
        });
        $this->should('update HomeHelpServiceCalcSpec using use case', function (): void {
            $this->editHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->homeHelpServiceCalcSpec->officeId,
                    $this->homeHelpServiceCalcSpec->id,
                    $this->inputUpdate()
                )
                ->andReturn($this->homeHelpServiceCalcSpec);
            app()->call(
                [$this->controller, 'update'],
                ['officeId' => $this->homeHelpServiceCalcSpec->officeId, 'id' => $this->homeHelpServiceCalcSpec->id]
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
            'officeId' => $this->homeHelpServiceCalcSpec->officeId,
            'period' => [
                'start' => $this->homeHelpServiceCalcSpec->period->start->toDateString(),
                'end' => $this->homeHelpServiceCalcSpec->period->end->toDateString(),
            ],
            'specifiedOfficeAddition' => $this->homeHelpServiceCalcSpec->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->specifiedTreatmentImprovementAddition->value(),
            'baseIncreaseSupportAddition' => $this->homeHelpServiceCalcSpec->baseIncreaseSupportAddition->value(),
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function inputUpdate(): array
    {
        return [
            'period' => $this->homeHelpServiceCalcSpec->period,
            'specifiedOfficeAddition' => $this->homeHelpServiceCalcSpec->specifiedOfficeAddition,
            'treatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->treatmentImprovementAddition,
            'specifiedTreatmentImprovementAddition' => $this->homeHelpServiceCalcSpec->specifiedTreatmentImprovementAddition,
            'baseIncreaseSupportAddition' => $this->homeHelpServiceCalcSpec->baseIncreaseSupportAddition,
        ];
    }
}
