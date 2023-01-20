<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\HomeVisitLongTermCareCalcSpecController;
use App\Http\Requests\CreateHomeVisitLongTermCareCalcSpecRequest;
use App\Http\Requests\GetHomeVisitLongTermCareCalcSpecRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateHomeVisitLongTermCareCalcSpecRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\EditHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\GetHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupHomeVisitLongTermCareCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * HomeVisitLongTermCareCalcSpecController のテスト.
 */
class HomeVisitLongTermCareCalcSpecControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use EditHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use ExamplesConsumer;
    use GetHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use LookupHomeVisitLongTermCareCalcSpecUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private HomeVisitLongTermCareCalcSpec $homeVisitLongTermCareCalcSpec;
    private Office $office;
    private HomeVisitLongTermCareCalcSpecController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HomeVisitLongTermCareCalcSpecControllerTest $self): void {
            $self->homeVisitLongTermCareCalcSpec = $self->examples->homeVisitLongTermCareCalcSpecs[0];
            $self->createHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn([
                    'homeVisitLongTermCareCalcSpec' => $self->examples->homeVisitLongTermCareCalcSpecs[0],
                    'provisionReportCount' => 1,
                ])
                ->byDefault();
            $self->editHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn([
                    'homeVisitLongTermCareCalcSpec' => $self->examples->homeVisitLongTermCareCalcSpecs[0],
                    'provisionReportCount' => 1,
                ])
                ->byDefault();
            $self->getHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->homeVisitLongTermCareCalcSpec))
                ->byDefault();
            $self->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->homeVisitLongTermCareCalcSpecs[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(HomeVisitLongTermCareCalcSpecController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/home-visit-long-term-care-calc-specs',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForCreate())
        ));
        app()->bind(CreateHomeVisitLongTermCareCalcSpecRequest::class, function () {
            $request = Mockery::mock(CreateHomeVisitLongTermCareCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 201 response', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'], ['officeId' => $this->examples->offices[0]->id])->getStatusCode()
            );
        });
        $this->should('return a response', function (): void {
            $this->assertSame(
                Json::encode(['homeVisitLongTermCareCalcSpec' => $this->homeVisitLongTermCareCalcSpec, 'provisionReportCount' => 1]),
                app()->call([$this->controller, 'create'], ['officeId' => $this->examples->offices[0]->id])->getContent()
            );
        });
        $this->should('create HomeVisitLongTermCareCalcSpec using use case', function (): void {
            $this->createHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->offices[0]->id, equalTo($this->createHomeVisitLongTermCareCalcSpec()))
                ->andReturn([
                    'homeVisitLongTermCareCalcSpec' => $this->homeVisitLongTermCareCalcSpec,
                    'provisionReportCount' => 1,
                ]);

            app()->call([$this->controller, 'create'], ['officeId' => $this->examples->offices[0]->id]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/home-visit-long-term-care-calc-specs/{id}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'get'], ['officeId' => $this->examples->offices[0]->id, 'id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of HomeHelpServiceCalcSpec', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId, 'id' => $this->homeVisitLongTermCareCalcSpec->id]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(
                Json::encode(['homeVisitLongTermCareCalcSpec' => $this->homeVisitLongTermCareCalcSpec], 0),
                $response->getContent()
            );
        });
        $this->should('get HomeVisitLongTermCareCalcSpec using use case', function () {
            $this->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->examples->offices[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[0]->id)
                ->andReturn(Seq::from($this->examples->homeVisitLongTermCareCalcSpecs[0]));

            app()->call([$this->controller, 'get'], ['officeId' => $this->examples->offices[0]->id, 'id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->homeVisitLongTermCareCalcSpec->officeId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId, 'id' => self::NOT_EXISTING_ID]);
                }
            );
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_identify(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{officeId}/home-visit-long-term-care-calc-specs',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['providedIn' => '2021-10'])
        ));
        app()->bind(GetHomeVisitLongTermCareCalcSpecRequest::class, function () {
            $request = Mockery::mock(GetHomeVisitLongTermCareCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'identify'],
                    ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON of HomeVisitLongTermCareCalcSpec', function (): void {
            $response = app()->call(
                [$this->controller, 'identify'],
                ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(
                Json::encode(['homeVisitLongTermCareCalcSpec' => $this->homeVisitLongTermCareCalcSpec], 0),
                $response->getContent()
            );
        });
        $this->should('get HomeVisitLongTermCareCalcSpec using use case', function () {
            $this->getHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::viewInternalOffices()], $this->examples->offices[0]->id, equalTo(Carbon::parse('2021-10')))
                ->andReturn(Option::some($this->examples->homeVisitLongTermCareCalcSpecs[0]));

            app()->call(
                [$this->controller, 'identify'],
                ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId]
            );
        });
        $this->should('throw a NotFoundException when GetHomeVisitLongTermCareCalcSpecUseCase return none', function (): void {
            $this->getHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call(
                        [$this->controller, 'identify'],
                        ['officeId' => $this->homeVisitLongTermCareCalcSpec->officeId]
                    );
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
            '/api/offices/{officeId}/home-visit-long-term-care-calc-specs/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForUpdate())
        ));
        app()->bind(UpdateHomeVisitLongTermCareCalcSpecRequest::class, function () {
            $request = Mockery::mock(UpdateHomeVisitLongTermCareCalcSpecRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->examples->offices[0]->id, 'id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id]
                )->getStatusCode()
            );
        });
        $this->should('return a response of Entity', function (): void {
            $this->assertSame(
                Json::encode(['homeVisitLongTermCareCalcSpec' => $this->homeVisitLongTermCareCalcSpec, 'provisionReportCount' => 1]),
                app()->call(
                    [$this->controller, 'update'],
                    ['officeId' => $this->examples->offices[0]->id, 'id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id]
                )->getContent()
            );
        });
        $this->should('update HomeVisitLongTermCareCalcSpec using use case', function (): void {
            $this->editHomeVisitLongTermCareCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0]->id,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->id,
                    equalTo($this->editHomeVisitLongTermCareCalcSpecValue())
                )
                ->andReturn([
                    'homeVisitLongTermCareCalcSpec' => $this->examples->homeVisitLongTermCareCalcSpecs[0],
                    'provisionReportCount' => 1,
                ]);
            app()->call(
                [$this->controller, 'update'],
                ['officeId' => $this->examples->offices[0]->id, 'id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id]
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editHomeVisitLongTermCareCalcSpecValue(): array
    {
        $input = $this->inputForUpdate();
        return [
            'period' => CarbonRange::create([
                'start' => Carbon::create($input['period']['start']),
                'end' => Carbon::create($input['period']['end']),
            ]),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($input['specifiedOfficeAddition']),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($input['treatmentImprovementAddition']),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($input['specifiedTreatmentImprovementAddition']),
            'locationAddition' => LtcsOfficeLocationAddition::from($input['locationAddition']),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($input['baseIncreaseSupportAddition']),
        ];
    }

    /**
     * リクエストから生成されるはずの事業所算定情報（介保・訪問介護）モデルインスタンス.
     *
     * @return \Domain\Office\HomeVisitLongTermCareCalcSpec
     */
    private function createHomeVisitLongTermCareCalcSpec(): HomeVisitLongTermCareCalcSpec
    {
        $input = $this->inputForCreate();
        return HomeVisitLongTermCareCalcSpec::create([
            'officeId' => $input['officeId'],
            'period' => CarbonRange::create([
                'start' => Carbon::create($input['period']['start']),
                'end' => Carbon::create($input['period']['end']),
            ]),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($input['specifiedOfficeAddition']),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($input['treatmentImprovementAddition']),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($input['specifiedTreatmentImprovementAddition']),
            'locationAddition' => LtcsOfficeLocationAddition::from($input['locationAddition']),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($input['baseIncreaseSupportAddition']),
        ]);
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function inputForCreate(): array
    {
        return [
            'officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
            'period' => [
                'start' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period->start->toDateString(),
                'end' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period->end->toDateString(),
            ],
            'specifiedOfficeAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition->value(),
            'locationAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition->value(),
            'baseIncreaseSupportAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition->value(),
        ];
    }

    /**
     * 更新用Input.
     *
     * @return array
     */
    private function inputForUpdate(): array
    {
        return [
            'period' => [
                'start' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period->start->toDateString(),
                'end' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period->end->toDateString(),
            ],
            'specifiedOfficeAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition->value(),
            'locationAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition->value(),
            'baseIncreaseSupportAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition->value(),
        ];
    }
}
