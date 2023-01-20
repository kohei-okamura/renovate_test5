<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\FindUserRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Jobs\EditUserLocationJob;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Location;
use Domain\Common\Pagination;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateUserUseCaseMixin;
use Tests\Unit\Mixins\EditUserBankAccountUseCaseMixin;
use Tests\Unit\Mixins\EditUserUseCaseMixin;
use Tests\Unit\Mixins\FindUserUseCaseMixin;
use Tests\Unit\Mixins\GetUserInfoUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupContractUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * UserController のテスト.
 */
final class UserControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateUserUseCaseMixin;
    use EditUserBankAccountUseCaseMixin;
    use EditUserUseCaseMixin;
    use ExamplesConsumer;
    use FindUserUseCaseMixin;
    use JobsDispatcherMixin;
    use LookupUserUseCaseMixin;
    use LookupContractUseCaseMixin;
    use GetUserInfoUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use RequestMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use RoleRepositoryMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [
        'q' => '内藤勇介',
    ];

    public const PAGINATION_PARAMS = [
        'sortBy' => 'date',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private UserController $controller;
    private FinderResult $finderResult;
    private array $returnValue;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->createUserUseCase->allows('handle')->andReturn($self->examples->users[0])->byDefault();
            $self->editUserUseCase->allows('handle')->andReturn($self->examples->users[0])->byDefault();
            $self->lookupUserUseCase->allows('handle')->andReturn(Seq::from($self->examples->users[0]))->byDefault();
            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->users, $pagination);
            $self->findUserUseCase->allows('handle')->andReturn($self->finderResult)->byDefault();
            $self->returnValue = [
                'bankAccount' => $self->examples->bankAccounts[0],
                'contracts' => $self->examples->contracts,
                'dwsCertifications' => $self->examples->dwsCertifications[0],
                'ltcsInsCards' => $self->examples->ltcsInsCards[0],
                'user' => $self->examples->users[0],
            ];
            $self->getUserInfoUseCase->allows('handle')->andReturn($self->returnValue)->byDefault();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]));
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(UserController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/users',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(CreateUserRequest::class, function () {
            $request = Mockery::mock(CreateUserRequest::class)->makePartial();
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
        $this->should('create User using use case', function (): void {
            $this->createUserUseCase
                ->expects('handle')
                // ここでは、Closureの検証ができないため any で通す
                ->with($this->context, equalTo($this->createUserModelInstance()), Mockery::any())
                ->andReturnUsing(function (Context $context, User $user, callable $f) {
                    // 引数が Domain\User\User かの検証
                    $f($user);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(EditUserLocationJob::class);
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
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/users/{id}',
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
                app()->call([$this->controller, 'get'], ['id' => $this->examples->users[0]->id])->getStatusCode()
            );
        });
        $this->should('return a JSON of User', function (): void {
            $response = app()->call([$this->controller, 'get'], ['id' => $this->examples->users[0]->id]);

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

            $this->assertSame(Json::encode($this->returnValue, \JSON_UNESCAPED_UNICODE), $response->getContent());
        });
        $this->should('get UserInfo using use case', function (): void {
            $this->getUserInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->id)
                ->andReturn($this->returnValue);

            app()->call([$this->controller, 'get'], ['id' => $this->examples->users[0]->id]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => IlluminateRequest::create(
            '/app/users',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindUserRequest::class, function () {
            $request = Mockery::mock(FindUserRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn(self::FILTER_PARAMS)->byDefault();
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
        $this->should('find users using use case', function (): void {
            $this->findUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::listUsers(), self::FILTER_PARAMS, self::PAGINATION_PARAMS)
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
        app()->bind('request', fn () => IlluminateRequest::create(
            '/api/users/{id}',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->input())
        ));
        app()->bind(UpdateUserRequest::class, function () {
            $request = Mockery::mock(UpdateUserRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'update'], ['id' => $this->examples->users[0]->id])->getStatusCode()
            );
        });
        $this->should('return an response of entity', function (): void {
            $user = $this->examples->users[0];

            $response = app()->call([$this->controller, 'update'], ['id' => $user->id]);

            $this->assertSame(Json::encode(compact('user'), 0), $response->getContent());
        });
        $this->should('update User using use case', function (): void {
            $this->editUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    equalTo($this->editUserValue()),
                    // ここでは、Closureの検証ができないため any で通す
                    Mockery::any()
                )
                ->andReturnUsing(function (Context $context, int $id, array $value, callable $f) {
                    $user = User::create($value);
                    // 引数が Domain\User\User かの検証
                    $f($user);
                    // dispatch を呼ぶ Closure かの検証
                    $this->dispatcher->assertDispatched(EditUserLocationJob::class);

                    return $this->examples->users[0];
                });

            app()->call([$this->controller, 'update'], ['id' => $this->examples->users[0]->id]);
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editUserValue(): array
    {
        $input = $this->input();
        return [
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
                prefecture: Prefecture::saitama(),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'],
            ),
            'contacts' => Seq::fromArray($input['contacts'])
                ->map(fn (array $x): Contact => Contact::create([
                    'tel' => $x['tel'],
                    'relationship' => ContactRelationship::from($x['relationship']),
                    'name' => $x['name'],
                ]))
                ->toArray(),
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::from($input['billingDestination']['destination']),
                'paymentMethod' => PaymentMethod::from($input['billingDestination']['paymentMethod']),
                'contractNumber' => $input['billingDestination']['paymentMethod'] === PaymentMethod::withdrawal()->value()
                    ? $input['billingDestination']['contractNumber']
                    : '',
                'corporationName' => $input['billingDestination']['destination'] === BillingDestination::corporation()->value()
                    ? $input['billingDestination']['corporationName']
                    : '',
                'agentName' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['agentName']
                    : '',
                'addr' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? new Addr(
                        postcode: $input['billingDestination']['postcode'],
                        prefecture: Prefecture::from($input['billingDestination']['prefecture']),
                        city: $input['billingDestination']['city'],
                        street: $input['billingDestination']['street'],
                        apartment: $input['billingDestination']['apartment'],
                    )
                    : null,
                'tel' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['tel']
                    : '',
            ]),
            'isEnabled' => $input['isEnabled'],
        ];
    }

    /**
     * 登録用Input.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'familyName' => '新垣',
            'givenName' => '栄作',
            'phoneticFamilyName' => 'シンガキ',
            'phoneticGivenName' => 'エイサク',
            'sex' => Sex::male()->value(),
            'birthday' => '1982-05-09',
            'postcode' => '123-4567',
            'prefecture' => Prefecture::saitama()->value(),
            'city' => '和光市',
            'street' => '広沢',
            'apartment' => 'コーポXXX 202号室',
            'location' => [
                'lat' => null,
                'lng' => null,
            ],
            'contacts' => [
                [
                    'tel' => '01-2345-6789',
                    'relationship' => ContactRelationship::family()->value(),
                    'name' => '田中花子',
                ],
            ],
            'email' => 'example@mail.com',
            'billingDestination' => [
                'destination' => $this->examples->users[0]->billingDestination->destination->value(),
                'paymentMethod' => $this->examples->users[0]->billingDestination->paymentMethod->value(),
                'contractNumber' => $this->examples->users[0]->billingDestination->contractNumber,
                'corporationName' => $this->examples->users[0]->billingDestination->corporationName,
                'agentName' => $this->examples->users[0]->billingDestination->agentName,
                'postcode' => $this->examples->users[0]->billingDestination->addr->postcode,
                'prefecture' => $this->examples->users[0]->billingDestination->addr->prefecture->value(),
                'city' => $this->examples->users[0]->billingDestination->addr->city,
                'street' => $this->examples->users[0]->billingDestination->addr->street,
                'apartment' => $this->examples->users[0]->billingDestination->addr->apartment,
                'tel' => $this->examples->users[0]->billingDestination->tel,
            ],
            'isEnabled' => true,
        ];
    }

    /**
     * リクエストから生成されるはずの利用者.
     *
     * @return \Domain\User\User
     */
    private function createUserModelInstance(): User
    {
        $input = $this->input();
        $overwrites = [
            'name' => new StructuredName(
                familyName: $input['familyName'],
                givenName: $input['givenName'],
                phoneticFamilyName: $input['phoneticFamilyName'],
                phoneticGivenName: $input['phoneticGivenName'],
            ),
            'addr' => new Addr(
                postcode: $input['postcode'],
                prefecture: Prefecture::saitama(),
                city: $input['city'],
                street: $input['street'],
                apartment: $input['apartment'],
            ),
            'location' => Location::create([
                'lat' => null,
                'lng' => null,
            ]),
            'contacts' => Seq::fromArray($input['contacts'])
                ->map(fn (array $x): Contact => Contact::create([
                    'tel' => $x['tel'],
                    'relationship' => ContactRelationship::from($x['relationship']),
                    'name' => $x['name'],
                ]))
                ->toArray(),
            'sex' => Sex::from($input['sex']),
            'birthday' => Carbon::parse($input['birthday']),
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::from($input['billingDestination']['destination']),
                'paymentMethod' => PaymentMethod::from($input['billingDestination']['paymentMethod']),
                'contractNumber' => $input['billingDestination']['paymentMethod'] === PaymentMethod::withdrawal()->value()
                    ? $input['billingDestination']['contractNumber']
                    : '',
                'corporationName' => $input['billingDestination']['destination'] === BillingDestination::corporation()->value()
                    ? $input['billingDestination']['corporationName']
                    : '',
                'agentName' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['agentName']
                    : '',
                'addr' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? new Addr(
                        postcode: $input['billingDestination']['postcode'],
                        prefecture: Prefecture::from($input['billingDestination']['prefecture']),
                        city: $input['billingDestination']['city'],
                        street: $input['billingDestination']['street'],
                        apartment: $input['billingDestination']['apartment'],
                    )
                    : null,
                'tel' => $input['billingDestination']['destination'] !== BillingDestination::theirself()->value()
                    ? $input['billingDestination']['tel']
                    : '',
            ]),
            'isEnabled' => true,
        ];
        return User::create($overwrites + $input);
    }
}
