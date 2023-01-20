<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsCertificationController;
use App\Http\Requests\CreateDwsCertificationRequest;
use App\Http\Requests\DeleteDwsCertificationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsCertificationRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\DeleteDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\EditDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsCertificationController} のテスト.
 */
final class DwsCertificationControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateDwsCertificationUseCaseMixin;
    use DwsBillingStatementFinderMixin;
    use EditDwsCertificationUseCaseMixin;
    use DeleteDwsCertificationUseCaseMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use LookupDwsCertificationUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private DwsCertificationController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->createDwsCertificationUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsCertifications[0])
                ->byDefault();
            $self->editDwsCertificationUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsCertifications[0])
                ->byDefault();
            $self->deleteDwsCertificationUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsCertifications[0])
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from([], Pagination::create()))
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
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
            $self->controller = app(DwsCertificationController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/users/{userId}/dws-certifications',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateDwsCertificationRequest::class, function () {
            $request = Mockery::mock(CreateDwsCertificationRequest::class)->makePartial();
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
        $this->should('create DwsCertification using use case', function (): void {
            $this->createDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    equalTo(DwsCertification::create($this->payloadForCreate()))
                )
                ->andReturn($this->examples->dwsCertifications[0]);
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
            '/api/users/{userId}/dws-certifications/{id}',
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
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewDwsCertifications(),
                    $this->examples->users[0]->id,
                    self::NOT_EXISTING_ID
                )->andReturn(Seq::empty());
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
        $this->should('return a JSON of DwsCertification', function (): void {
            $dwsCertification = $this->examples->dwsCertifications[0];
            $response = app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $dwsCertification->id]
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('dwsCertification')), $response->getContent());
        });
        $this->should('get DwsCertification using use case', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewDwsCertifications(),
                    $this->examples->users[0]->id,
                    $this->examples->dwsCertifications[0]->id
                )
                ->andReturn(Seq::from($this->examples->dwsCertifications[0]));
            app()->call(
                [$this->controller, 'get'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->dwsCertifications[0]->id]
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
            '/users/{userId}/dws-certifications/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateDwsCertificationRequest::class, function () {
            $request = Mockery::mock(UpdateDwsCertificationRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $response = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->dwsCertifications[0]->id]
            );
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        });
        $this->should('return an response of entity', function (): void {
            $dwsCertification = $this->examples->dwsCertifications[0];

            $response = app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $dwsCertification->id]
            );

            $this->assertSame(Json::encode(compact('dwsCertification'), 0), $response->getContent());
        });
        $this->should('update DwsCertification using use case', function (): void {
            $this->editDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->dwsCertifications[0]->id,
                    $this->payloadForUpdate()
                )
                ->andReturn($this->examples->dwsCertifications[0]);
            app()->call(
                [$this->controller, 'update'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->dwsCertifications[0]->id]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_delete(): void
    {
        app()->bind('request', fn () => Request::create(
            '/users/{userId}/dws-certifications/{id}',
            'DELETE',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(DeleteDwsCertificationRequest::class, function () {
            $request = Mockery::mock(DeleteDwsCertificationRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $response = app()->call(
                [$this->controller, 'delete'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->dwsCertifications[0]->id]
            );
            $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        });
        $this->should('return an empty response', function (): void {
            $response = app()->call(
                [$this->controller, 'delete'],
                ['userId' => $this->examples->users[0]->id, 'id' => $this->examples->dwsCertifications[0]->id]
            );
            $this->assertSame('', $response->getContent());
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
            'dwsLevel' => $this->examples->dwsCertifications[0]->dwsLevel->value(),
            'status' => $this->examples->dwsCertifications[0]->status->value(),
            'dwsTypes' => Seq::fromArray($this->examples->dwsCertifications[0]->dwsTypes)
                ->map(fn (DwsType $x): int => $x->value())
                ->toArray(),
            'copayCoordination' => [
                'copayCoordinationType' => $this->examples->dwsCertifications[0]->copayCoordination->copayCoordinationType->value(),
                'officeId' => $this->examples->offices[0]->id,
            ],
            'dwsNumber' => $this->examples->dwsCertifications[0]->dwsNumber,
            'cityCode' => $this->examples->dwsCertifications[0]->cityCode,
            'cityName' => $this->examples->dwsCertifications[0]->cityName,
            'child' => [
                'name' => [
                    'familyName' => '内藤',
                    'givenName' => '勇介',
                    'phoneticFamilyName' => 'ナイトウ',
                    'phoneticGivenName' => 'ユウスケ',
                ],
                'birthday' => '1985-02-24',
            ],
            'copayRate' => $this->examples->dwsCertifications[0]->copayRate,
            'copayLimit' => $this->examples->dwsCertifications[0]->copayLimit,
            'isSubjectOfComprehensiveSupport' => $this->examples->dwsCertifications[0]->isSubjectOfComprehensiveSupport,
            'issuedOn' => $this->examples->dwsCertifications[0]->issuedOn->toDateString(),
            'effectivatedOn' => $this->examples->dwsCertifications[0]->effectivatedOn->toDateString(),
            'activatedOn' => $this->examples->dwsCertifications[0]->activatedOn->toDateString(),
            'deactivatedOn' => $this->examples->dwsCertifications[0]->deactivatedOn->toDateString(),
            'copayActivatedOn' => $this->examples->dwsCertifications[0]->copayActivatedOn->toDateString(),
            'copayDeactivatedOn' => $this->examples->dwsCertifications[0]->copayDeactivatedOn->toDateString(),
        ];
        $agreements = Seq::from($this->examples->dwsCertifications[0]->agreements[0])
            ->map(fn (DwsCertificationAgreement $agreement) => [
                'indexNumber' => $agreement->indexNumber,
                'officeId' => $agreement->officeId,
                'dwsCertificationAgreementType' => DwsCertificationAgreementType::accompany()->value(),
                'paymentAmount' => $agreement->paymentAmount,
                'agreedOn' => $agreement->agreedOn->toDateString(),
                'expiredOn' => $agreement->expiredOn->toDateString(),
            ])
            ->toArray();
        $grants = Seq::fromArray($this->examples->dwsCertifications[0]->grants)
            ->map(fn (DwsCertificationGrant $grant) => [
                'dwsCertificationServiceType' => $grant->dwsCertificationServiceType->value(),
                'grantedAmount' => $grant->grantedAmount,
                'activatedOn' => $grant->activatedOn->toDateString(),
                'deactivatedOn' => $grant->deactivatedOn->toDateString(),
            ])
            ->toArray();

        return $values + compact('agreements', 'grants');
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payloadForCreate(): array
    {
        $input = $this->input();
        $values = [
            'dwsLevel' => DwsLevel::from($input['dwsLevel']),
            'status' => DwsCertificationStatus::from($input['status']),
            'dwsTypes' => Seq::fromArray($input['dwsTypes'])->map(fn (int $x): DwsType => DwsType::from($x))->toArray(),
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => CopayCoordinationType::from($input['copayCoordination']['copayCoordinationType']),
                'officeId' => $input['copayCoordination']['officeId'],
            ]),
            'child' => Child::create([
                'name' => new StructuredName(
                    familyName: $input['child']['name']['familyName'],
                    givenName: $input['child']['name']['givenName'],
                    phoneticFamilyName: $input['child']['name']['phoneticFamilyName'],
                    phoneticGivenName: $input['child']['name']['phoneticGivenName'],
                ),
                'birthday' => Carbon::parse($input['child']['birthday']),
            ]),
            'isSubjectOfComprehensiveSupport' => $input['isSubjectOfComprehensiveSupport'],
            'issuedOn' => Carbon::parse($input['issuedOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'activatedOn' => Carbon::parse($input['activatedOn']),
            'deactivatedOn' => Carbon::parse($input['deactivatedOn']),
            'copayActivatedOn' => Carbon::parse($input['copayActivatedOn']),
            'copayDeactivatedOn' => Carbon::parse($input['copayDeactivatedOn']),
            'isEnabled' => true,
        ];
        $agreements = Seq::fromArray($input['agreements'])
            ->map(fn (array $x): DwsCertificationAgreement => DwsCertificationAgreement::create([
                'indexNumber' => $x['indexNumber'],
                'officeId' => $x['officeId'],
                'dwsCertificationAgreementType' => DwsCertificationAgreementType::from($x['dwsCertificationAgreementType']),
                'paymentAmount' => $x['paymentAmount'],
                'agreedOn' => Carbon::parse($x['agreedOn']),
                'expiredOn' => Carbon::parse($x['expiredOn']),
            ]))
            ->toArray();
        $grants = Seq::fromArray($input['grants'])
            ->map(fn (array $x): DwsCertificationGrant => DwsCertificationGrant::create([
                'dwsCertificationServiceType' => DwsCertificationServiceType::from($x['dwsCertificationServiceType']),
                'grantedAmount' => $x['grantedAmount'],
                'activatedOn' => Carbon::parse($x['activatedOn']),
                'deactivatedOn' => Carbon::parse($x['deactivatedOn']),
            ]))
            ->toArray();

        return $values + compact('agreements', 'grants') + $input;
    }

    /**
     * payload が返す配列を生成.
     *
     * @return array
     */
    private function payloadForUpdate(): array
    {
        $input = tap($this->input(), function (array &$defaultInput): void {
            Arr::forget($defaultInput, 'copayRate');
        });
        $values = [
            'dwsLevel' => DwsLevel::from($input['dwsLevel']),
            'status' => DwsCertificationStatus::from($input['status']),
            'dwsTypes' => Seq::fromArray($input['dwsTypes'])->map(fn (int $x): DwsType => DwsType::from($x))->toArray(),
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => CopayCoordinationType::from($input['copayCoordination']['copayCoordinationType']),
                'officeId' => $input['copayCoordination']['officeId'],
            ]),
            'child' => Child::create([
                'name' => new StructuredName(
                    familyName: $input['child']['name']['familyName'],
                    givenName: $input['child']['name']['givenName'],
                    phoneticFamilyName: $input['child']['name']['phoneticFamilyName'],
                    phoneticGivenName: $input['child']['name']['phoneticGivenName'],
                ),
                'birthday' => Carbon::parse($input['child']['birthday']),
            ]),
            'isSubjectOfComprehensiveSupport' => $input['isSubjectOfComprehensiveSupport'],
            'issuedOn' => Carbon::parse($input['issuedOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'activatedOn' => Carbon::parse($input['activatedOn']),
            'deactivatedOn' => Carbon::parse($input['deactivatedOn']),
            'copayActivatedOn' => Carbon::parse($input['copayActivatedOn']),
            'copayDeactivatedOn' => Carbon::parse($input['copayDeactivatedOn']),
            'isEnabled' => true,
        ];
        $agreements = Seq::fromArray($input['agreements'])
            ->map(fn (array $x): DwsCertificationAgreement => DwsCertificationAgreement::create([
                'indexNumber' => $x['indexNumber'],
                'officeId' => $x['officeId'],
                'dwsCertificationAgreementType' => DwsCertificationAgreementType::from($x['dwsCertificationAgreementType']),
                'paymentAmount' => $x['paymentAmount'],
                'agreedOn' => Carbon::parse($x['agreedOn']),
                'expiredOn' => Carbon::parse($x['expiredOn']),
            ]))
            ->toArray();
        $grants = Seq::fromArray($input['grants'])
            ->map(fn (array $x): DwsCertificationGrant => DwsCertificationGrant::create([
                'dwsCertificationServiceType' => DwsCertificationServiceType::from($x['dwsCertificationServiceType']),
                'grantedAmount' => $x['grantedAmount'],
                'activatedOn' => Carbon::parse($x['activatedOn']),
                'deactivatedOn' => Carbon::parse($x['deactivatedOn']),
            ]))
            ->toArray();

        return $values + compact('agreements', 'grants') + $input;
    }
}
