<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\OfficeController;
use App\Http\Requests\CreateOfficeRequest;
use App\Http\Requests\FindOfficeRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Jobs\EditOfficeLocationJob;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Pagination;
use Domain\Common\Prefecture;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateOfficeUseCaseMixin;
use Tests\Unit\Mixins\EditOfficeUseCaseMixin;
use Tests\Unit\Mixins\GetIndexOfficeUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeInfoUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupDwsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * OfficeController のテスト.
 */
final class OfficeControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateOfficeUseCaseMixin;
    use EditOfficeUseCaseMixin;
    use ExamplesConsumer;
    use GetIndexOfficeUseCaseMixin;
    use GetOfficeInfoUseCaseMixin;
    use GetOfficeListUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupDwsAreaGradeUseCaseMixin;
    use LookupLtcsAreaGradeUseCaseMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [
        'q' => '事業所テスト',
    ];

    public const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private OfficeController $controller;
    private Office $office;
    private OfficeGroup $officeGroup;
    private array $officeInfo;

    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->createOfficeUseCase
                ->allows('handle')
                ->andReturn($self->examples->offices[0])
                ->byDefault();

            $self->officeInfo = [
                'office' => $self->examples->offices[0],
            ];
            $self->getOfficeInfoUseCase
                ->allows('handle')
                ->andReturn($self->officeInfo)
                ->byDefault();

            $self->getOfficeListUseCase
                ->allows('handle')
                ->with($self->context)
                ->andReturn(Seq::fromArray($self->examples->offices))
                ->byDefault();

            $self->lookupDwsAreaGradeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsAreaGrades[0]))
                ->byDefault();

            $self->lookupLtcsAreaGradeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsAreaGrades[0]))
                ->byDefault();

            $self->editOfficeUseCase
                ->allows('handle')
                ->andReturn($self->examples->offices[0])
                ->byDefault();

            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();

            $self->organizationResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->offices, $pagination);
            $self->getIndexOfficeUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->staffs[0]));

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(OfficeController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->defaultInput())
        ));
        app()->bind(CreateOfficeRequest::class, function () {
            $request = Mockery::mock(CreateOfficeRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
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
        $this->should('create Office using use case', function (): void {
            $this->createOfficeUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, equalTo(Office::create($this->payload())), Mockery::any())
                ->andReturnUsing(function (Context $context, Office $office, callable $f) {
                    // 引数が Domain\Office\Office かの検証
                    $f($office);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(EditOfficeLocationJob::class);
                    return $this->examples->offices[0];
                });

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
            '/api/offices/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->examples->offices[0]->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of Office', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->examples->offices[0]->id]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode($this->officeInfo), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $this->getOfficeInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->offices[0]->id)
                ->andReturn(['office' => $this->examples->offices[0]]);

            app()->call([$this->controller, 'get'], ['id' => $this->examples->offices[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->filterParams() + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindOfficeRequest::class, function () {
            $request = Mockery::mock(FindOfficeRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn($this->filterParams())->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
        $this->should('find Offices using use case', function (): void {
            $this->getIndexOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::listInternalOffices(), Permission::listExternalOffices()],
                    $this->filterParams(),
                    self::PAGINATION_PARAMS
                )
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/offices/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->defaultInput())
        ));
        app()->bind(UpdateOfficeRequest::class, function () {
            $request = Mockery::mock(UpdateOfficeRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->examples->offices[0]->id])->getStatusCode()
            );
        });
        $this->should('return a Updated Entity of Office', function (): void {
            $office = $this->examples->offices[0];

            $response = app()->call([$this->controller, 'update'], ['id' => $this->examples->offices[0]->id]);

            $this->assertSame(Json::encode(compact('office'), 0), $response->getContent());
        });
        $this->should('update Office using use case', function (): void {
            $this->editOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0]->id,
                    equalTo($this->updatePayload()),
                    // ここでは、Closureの検証ができないため any で通す
                    Mockery::any()
                )
                ->andReturnUsing(function (Context $context, int $id, array $value, callable $f) {
                    $office = Office::create($value);
                    // 引数が Domain\Office\Office かの検証
                    $f($office);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(EditOfficeLocationJob::class);
                    return $this->examples->offices[0];
                });

            app()->call([$this->controller, 'update'], ['id' => $this->examples->offices[0]->id]);
        });
    }

    /**
     * Input.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        $values = [
            'name' => $this->examples->offices[0]->name,
            'abbr' => $this->examples->offices[0]->abbr,
            'phoneticName' => 'ツチヤホウモンカイゴジギョウショサッポロ',
            'corporationName' => $this->examples->offices[0]->corporationName,
            'phoneticCorporationName' => $this->examples->offices[0]->phoneticCorporationName,
            'purpose' => $this->examples->offices[0]->purpose->value(),
            'postcode' => '123-4567',
            'prefecture' => $this->examples->offices[0]->addr->prefecture->value(),
            'city' => $this->examples->offices[0]->addr->city,
            'street' => $this->examples->offices[0]->addr->street,
            'apartment' => $this->examples->offices[0]->addr->apartment,
            'tel' => '03-1234-5678',
            'fax' => '03-9876-5432',
            'email' => 'test@mail.com',
            'officeGroupId' => $this->examples->offices[0]->officeGroupId,
            'dwsGenericService' => [
                'dwsAreaGradeId' => $this->examples->offices[0]->dwsGenericService->dwsAreaGradeId,
                'code' => $this->examples->offices[0]->dwsGenericService->code,
                'openedOn' => $this->examples->offices[0]->dwsGenericService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->dwsGenericService->designationExpiredOn->toDateString(),
            ],
            'dwsCommAccompanyService' => [
                'code' => $this->examples->offices[0]->dwsGenericService->code,
                'openedOn' => $this->examples->offices[0]->dwsGenericService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->dwsGenericService->designationExpiredOn->toDateString(),
            ],
            'ltcsCareManagementService' => [
                'ltcsAreaGradeId' => $this->examples->offices[0]->ltcsCareManagementService->ltcsAreaGradeId,
                'code' => $this->examples->offices[0]->ltcsCareManagementService->code,
                'openedOn' => $this->examples->offices[0]->ltcsCareManagementService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsCareManagementService->designationExpiredOn->toDateString(),
            ],
            'ltcsHomeVisitLongTermCareService' => [
                'ltcsAreaGradeId' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                'code' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->code,
                'openedOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->designationExpiredOn->toDateString(),
            ],
            'ltcsCompHomeVisitingService' => [
                'code' => $this->examples->offices[0]->ltcsCompHomeVisitingService->code,
                'openedOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->openedOn->toDateString(),
                'designationExpiredOn' => $this->examples->offices[0]->ltcsCompHomeVisitingService->designationExpiredOn->toDateString(),
            ],
            'status' => OfficeStatus::inOperation()->value(),
            'isEnabled' => $this->examples->offices[0]->isEnabled,
        ];
        $qualifications = Seq::fromArray($this->examples->offices[0]->qualifications)
            ->map(fn (OfficeQualification $qualification) => $qualification->value())
            ->toArray();

        return $values + compact('qualifications');
    }

    /**
     * payload.
     *
     * @return array
     */
    private function payload(): array
    {
        $input = $this->defaultInput();
        return [
            'name' => $input['name'],
            'abbr' => $input['abbr'],
            'phoneticName' => $input['phoneticName'],
            'corporationName' => '',
            'phoneticCorporationName' => '',
            'purpose' => Purpose::from($input['purpose']),
            'addr' => new Addr(
                postcode: $input['postcode'],
                prefecture: Prefecture::from($input['prefecture']),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'],
            ),
            'location' => Location::create([
                'lat' => 0,
                'lng' => 0,
            ]),
            'tel' => $input['tel'],
            'fax' => $input['fax'],
            'email' => $input['email'],
            'qualifications' => Seq::fromArray($input['qualifications'])
                ->map(fn (string $x): OfficeQualification => OfficeQualification::from($x))
                ->toArray(),
            'officeGroupId' => $input['officeGroupId'],
            // office[0] の qualifications が dwsHomeHelpService, dwsOthers, ltcsHomeVisitLongTermCare のため
            // dwsGenericService, ltcsHomeVisitLongTermCareService のみを設定する
            'dwsGenericService' => OfficeDwsGenericService::create($input['dwsGenericService']),
            'dwsCommAccompanyService' => null,
            'ltcsCareManagementService' => null,
            'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create(
                $input['ltcsHomeVisitLongTermCareService']
            ),
            'ltcsCompHomeVisitingService' => null,
            'ltcsPreventionService' => null,
            'status' => OfficeStatus::from($input['status']),
            'isEnabled' => $input['isEnabled'],
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
    }

    /**
     * update アクションメソッドの payload が返す配列.
     *
     * @return array
     */
    private function updatePayload(): array
    {
        $payload = $this->payload();
        $excludes = [
            'location',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
        return Arr::except($payload, $excludes);
    }

    /**
     * 検索項目の定義.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'q' => '事業所テスト',
            'prefecture' => Prefecture::tokyo()->value(),
        ];
    }
}
