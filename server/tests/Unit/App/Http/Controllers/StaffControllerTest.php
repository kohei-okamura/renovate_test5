<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\StaffController;
use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\FindStaffDistanceRequest;
use App\Http\Requests\FindStaffRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Pagination;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Staff\Certification;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Illuminate\Http\Request as IlluminateRequest;
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
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateStaffWithInvitationUseCaseMixin;
use Tests\Unit\Mixins\EditStaffUseCaseMixin;
use Tests\Unit\Mixins\FindStaffDistanceUseCaseMixin;
use Tests\Unit\Mixins\FindStaffUseCaseMixin;
use Tests\Unit\Mixins\GetStaffInfoUseCaseMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationByTokenUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\VerifyStaffEmailUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\StaffController} のテスト.
 */
final class StaffControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateStaffWithInvitationUseCaseMixin;
    use EditStaffUseCaseMixin;
    use ExamplesConsumer;
    use FindStaffDistanceUseCaseMixin;
    use FindStaffUseCaseMixin;
    use GetStaffInfoUseCaseMixin;
    use LookupContractUseCaseMixin;
    use LookupInvitationByTokenUseCaseMixin;
    use LookupInvitationUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use VerifyStaffEmailUseCaseMixin;

    private const FILTER_PARAMS = [
        'q' => '内藤勇介',
    ];
    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];
    private const TOKEN = '123456789012345678901234567890123456789012345678901234567890';

    private FinderResult $finderResult;
    private array $returnValue;

    private StaffController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->finderResult = FinderResult::from(
                $self->examples->staffs,
                Pagination::create(self::PAGINATION_PARAMS)
            );
            $self->returnValue = [
                'bankAccount' => $self->examples->bankAccounts[0],
                'offices' => $self->examples->offices[0],
                'roles' => $self->examples->roles[0],
                'staff' => $self->examples->staffs[0],
            ];

            $self->createStaffWithInvitationUseCase
                ->allows('handle')
                ->andReturn($self->examples->staffEmailVerifications[0])
                ->byDefault();

            $self->editStaffUseCase
                ->allows('handle')
                ->andReturn($self->returnValue)
                ->byDefault();

            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();

            $self->findStaffUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->findStaffDistanceUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->lookupInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();

            $self->lookupInvitationByTokenUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->invitations[0]))
                ->byDefault();

            $self->identifyStaffByEmailUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();

            $self->verifyStaffEmailUseCase
                ->allows('handle')
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $self->getStaffInfoUseCase
                ->allows('handle')
                ->andReturn($self->returnValue)
                ->byDefault();

            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]));

            $self->controller = app(StaffController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staff',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForCreate())
        ));
        app()->bind(CreateStaffRequest::class, function () {
            $request = Mockery::mock(CreateStaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return the response that has http status created', function (): void {
            $this->assertSame(
                Response::HTTP_CREATED,
                app()->call([$this->controller, 'create'])->getStatusCode()
            );
        });
        $this->should('return the response that content is empty', function (): void {
            $response = app()->call([$this->controller, 'create'])->getContent();
            $this->assertEmpty(json_decode($response, true));
        });
        $this->should('create staff by useCase', function (): void {
            $input = $this->inputForCreate();
            $this->createStaffWithInvitationUseCase
                ->expects('handle')
                ->andReturnUsing(function (Context $context, $invitationId, Staff $staff) use ($input): void {
                    $this->assertSame($this->context, $context);
                    $this->assertSame($input['invitationId'], $invitationId);
                    $this->assertModelStrictEquals($this->staff(), $staff);
                });

            app()->call([$this->controller, 'create']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staff/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->inputForUpdate())
        ));
        app()->bind(UpdateStaffRequest::class, function () {
            $request = Mockery::mock(UpdateStaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->examples->staffs[0]->id])->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $staff = $this->examples->staffs[0];

            $response = app()->call([$this->controller, 'update'], ['id' => $staff->id]);

            $this->assertSame(Json::encode($this->returnValue, 0), $response->getContent());
        });
        $this->should('update Staff using use case', function (): void {
            $this->editStaffUseCase
                ->expects('handle')
                // todo DEV-2421 [password] hashedValue が一致しないので with(. . $this->payload()) でチェックできない
//                ->with($this->context, $this->examples->staffs[0]->id, $this->payload())
                ->andReturn([]);

            app()->call([$this->controller, 'update'], ['id' => $this->examples->staffs[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staffs',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindStaffRequest::class, function () {
            $request = Mockery::mock(FindStaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn(self::FILTER_PARAMS)->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('find staffs using use case', function (): void {
            $this->findStaffUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::listStaffs(),
                    self::FILTER_PARAMS,
                    self::PAGINATION_PARAMS
                )
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staffs/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->examples->staffs[0]->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of Staff', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->examples->staffs[0]->id]);

            $this->assertSame(Json::encode($this->returnValue, \JSON_UNESCAPED_UNICODE), $response->getContent());
        });
        $this->should('get StaffInfo using use case', function (): void {
            $this->getStaffInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->staffs[0]->id)
                ->andReturn($this->returnValue);

            app()->call([$this->controller, 'get'], ['id' => $this->examples->staffs[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_distances(): void
    {
        $filterParams = [
            'userId' => $this->examples->users[15]->id,
            'sex' => Sex::male()->value(),
            'range' => 10000,
        ];
        $paginationParams = [
            'all' => true,
            'desc' => true,
            'itemsPerPage' => 10,
            'page' => 2,
            'sortBy' => 'distance',
        ];
        $params = $filterParams + $paginationParams;

        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staffs/distances',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($params)
        ));
        app()->bind(FindStaffDistanceRequest::class, function () use ($filterParams, $paginationParams) {
            $request = Mockery::mock(FindStaffDistanceRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn($filterParams)->byDefault();
            $request->allows('paginationParams')->andReturn($paginationParams)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'distances'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'distances'])->getContent()
            );
        });
        $this->should('find staff distances using use case', function () use ($filterParams, $paginationParams): void {
            $this->findStaffDistanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::listStaffs(), $filterParams, $paginationParams)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'distances']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_verify(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/staff-verifications/{token}',
            'PUT',
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
                app()->call([$this->controller, 'verify'], ['token' => self::TOKEN])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'verify'], ['token' => self::TOKEN])->getContent()
            );
        });
        $this->should('verify Staff using use case', function (): void {
            $this->verifyStaffEmailUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN);

            app()->call([$this->controller, 'verify'], ['token' => self::TOKEN]);
        });
        $this->should('not handle ForbiddenException when it was thrown', function (): void {
            $this->verifyStaffEmailUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andThrow(ForbiddenException::class);
            $this->assertThrows(
                ForbiddenException::class,
                function () {
                    app()->call([$this->controller, 'verify'], ['token' => self::TOKEN]);
                }
            );
        });
        $this->should('not handle NotFoundException when it was thrown', function (): void {
            $this->verifyStaffEmailUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andThrow(NotFoundException::class);
            $this->assertThrows(
                NotFoundException::class,
                function () {
                    app()->call([$this->controller, 'verify'], ['token' => self::TOKEN]);
                }
            );
        });
    }

    /**
     * Input.
     *
     * @return array
     */
    private function inputForCreate(): array
    {
        return [
            'password' => 'password',
            'familyName' => '内藤',
            'givenName' => '勇介',
            'phoneticFamilyName' => 'ナイトウ',
            'phoneticGivenName' => 'ユウスケ',
            'sex' => Sex::male()->value(),
            'birthday' => '1985-02-24',
            'postcode' => '123-4567',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '江戸川区',
            'street' => '北小岩2丁目 XX-XX',
            'apartment' => 'コーポXXX 202号室',
            'location' => [
                'lat' => 12.345678,
                'lng' => 123.456789,
            ],
            'tel' => '03-1234-5678',
            'fax' => '03-1234-5678',
            'certifications' => [],
            'status' => StaffStatus::provisional()->value(),
            'invitationId' => $this->examples->invitations[0]->id,
            'token' => $this->examples->invitations[0]->token,
        ];
    }

    /**
     * Input.
     *
     * @return array
     */
    private function inputForUpdate(): array
    {
        return [
            'email' => 'sample@example.com',
            'password' => 'password',
            'familyName' => '内藤',
            'givenName' => '勇介',
            'phoneticFamilyName' => 'ナイトウ',
            'phoneticGivenName' => 'ユウスケ',
            'sex' => Sex::male()->value(),
            'birthday' => '1985-02-24',
            'postcode' => '123-4567',
            'prefecture' => Prefecture::tokyo()->value(),
            'city' => '江戸川区',
            'street' => '北小岩2丁目 XX-XX',
            'apartment' => 'コーポXXX 202号室',
            'location' => [
                'lat' => 12.345678,
                'lng' => 123.456789,
            ],
            'tel' => '03-1234-5678',
            'fax' => '03-1234-5678',
            'status' => StaffStatus::active()->value(),
            'certifications' => [],
            'isEnabled' => true,
        ];
    }

    /**
     * リクエストから生成されるはずのスタッフ.
     *
     * @return \Domain\Staff\Staff
     */
    private function staff(): Staff
    {
        $input = $this->inputForCreate();
        return Staff::create([
            'roles' => [],
            'employeeNumber' => '',
            'name' => new StructuredName(
                familyName: $input['familyName'],
                givenName: $input['givenName'],
                phoneticFamilyName: $input['phoneticFamilyName'],
                phoneticGivenName: $input['phoneticGivenName'],
            ),
            'sex' => Sex::from($input['sex']),
            'birthday' => Carbon::parse($input['birthday']),
            'addr' => new Addr(
                postcode: $input['postcode'],
                prefecture: Prefecture::from($input['prefecture']),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'] ?? '',
            ),
            'location' => Location::create([
                'lat' => null,
                'lng' => null,
            ]),
            'tel' => $input['tel'],
            'fax' => $input['fax'] ?? '',
            'certifications' => Seq::fromArray($input['certifications'])
                ->map(fn (int $x): Certification => Certification::from($x))
                ->toArray(),
            'password' => Password::fromString($input['password']),
            'isVerified' => true,
            'status' => StaffStatus::from($input['status']),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }
}
