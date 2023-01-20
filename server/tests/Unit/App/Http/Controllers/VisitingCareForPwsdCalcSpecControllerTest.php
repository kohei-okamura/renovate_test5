<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\VisitingCareForPwsdCalcSpecController;
use App\Http\Requests\CreateVisitingCareForPwsdCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateVisitingCareForPwsdCalcSpecRequest;
use Domain\Office\VisitingCareForPwsdCalcSpec;
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
use Tests\Unit\Mixins\CreateVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\EditVisitingCareForPwsdCalcSpecMixin;
use Tests\Unit\Mixins\LookupVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * VisitingCareForPwsdCalcSpecController のテスト.
 */
class VisitingCareForPwsdCalcSpecControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateVisitingCareForPwsdCalcSpecUseCaseMixin;
    use EditVisitingCareForPwsdCalcSpecMixin;
    use ExamplesConsumer;
    use LookupVisitingCareForPwsdCalcSpecUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;
    private VisitingCareForPwsdCalcSpecController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (VisitingCareForPwsdCalcSpecControllerTest $self): void {
            $self->visitingCareForPwsdCalcSpec = $self->examples->visitingCareForPwsdCalcSpecs[0];

            $self->createVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn($self->visitingCareForPwsdCalcSpec)
                ->byDefault();

            $self->editVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn($self->visitingCareForPwsdCalcSpec)
                ->byDefault();

            $self->lookupVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->visitingCareForPwsdCalcSpec))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(VisitingCareForPwsdCalcSpecController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/visiting-care-for-pwsd-calc-specs',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputCreate())
        ));
        app()->bind(CreateVisitingCareForPwsdCalcSpecRequest::class, function () {
            $request = Mockery::mock(CreateVisitingCareForPwsdCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'create'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId])->getContent()
            );
        });
        $this->should('create VisitingCareForPwsdCalcSpec using use case', function (): void {
            $this->createVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->visitingCareForPwsdCalcSpec->officeId, equalTo(VisitingCareForPwsdCalcSpec::create(
                    [
                        'officeId' => $this->visitingCareForPwsdCalcSpec->officeId,
                        'period' => $this->visitingCareForPwsdCalcSpec->period,
                        'specifiedOfficeAddition' => $this->visitingCareForPwsdCalcSpec->specifiedOfficeAddition,
                        'treatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->treatmentImprovementAddition,
                        'specifiedTreatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->specifiedTreatmentImprovementAddition,
                        'baseIncreaseSupportAddition' => $this->visitingCareForPwsdCalcSpec->baseIncreaseSupportAddition,
                    ]
                )))
                ->andReturn($this->visitingCareForPwsdCalcSpec);

            app()->call([$this->controller, 'create'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{office_id}/visiting-care-for-pwsd-calc-specs/{id}',
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
                app()->call([$this->controller, 'get'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of VisitingCareForPwsdCalcSpec', function (): void {
            $response = app()->call([$this->controller, 'get'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(
                Json::encode(['visitingCareForPwsdCalcSpec' => $this->visitingCareForPwsdCalcSpec], 0),
                $response->getContent()
            );
        });
        $this->should('get VisitingCareForPwsdCalcSpec using use case', function (): void {
            $this->lookupVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec->id)
                ->andReturn(Seq::from($this->visitingCareForPwsdCalcSpec));

            app()->call([$this->controller, 'get'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => self::NOT_EXISTING_ID]);
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
            '/api/offices/{officeId}/visiting-care-for-pwsd-calc-specs/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputUpdate())
        ));
        app()->bind(UpdateVisitingCareForPwsdCalcSpecRequest::class, function () {
            $request = Mockery::mock(UpdateVisitingCareForPwsdCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $this->assertSame(
                Json::encode(['visitingCareForPwsdCalcSpec' => $this->visitingCareForPwsdCalcSpec], 0),
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id]
                )->getContent()
            );
        });
        $this->should('update VisitingCareForPwsdCalcSpec using use case', function (): void {
            $this->editVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->visitingCareForPwsdCalcSpec->officeId,
                    $this->visitingCareForPwsdCalcSpec->id,
                    $this->inputUpdate()
                )
                ->andReturn($this->visitingCareForPwsdCalcSpec);
            app()->call(
                [$this->controller, 'update'],
                ['officeId' => $this->visitingCareForPwsdCalcSpec->officeId, 'id' => $this->visitingCareForPwsdCalcSpec->id]
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
            'officeId' => $this->visitingCareForPwsdCalcSpec->officeId,
            'period' => [
                'start' => $this->visitingCareForPwsdCalcSpec->period->start->toDateString(),
                'end' => $this->visitingCareForPwsdCalcSpec->period->end->toDateString(),
            ],
            'specifiedOfficeAddition' => $this->visitingCareForPwsdCalcSpec->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->specifiedTreatmentImprovementAddition->value(),
            'baseIncreaseSupportAddition' => $this->visitingCareForPwsdCalcSpec->baseIncreaseSupportAddition->value(),
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
            'period' => $this->visitingCareForPwsdCalcSpec->period,
            'specifiedOfficeAddition' => $this->visitingCareForPwsdCalcSpec->specifiedOfficeAddition,
            'treatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->treatmentImprovementAddition,
            'specifiedTreatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->specifiedTreatmentImprovementAddition,
            'baseIncreaseSupportAddition' => $this->visitingCareForPwsdCalcSpec->baseIncreaseSupportAddition,
        ];
    }
}
