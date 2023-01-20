<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindUserBillingRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBillingUsedService\UserBillingUsedService;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\FindUserBillingRequest} のテスト.
 */
class FindUserBillingRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindUserBillingRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindUserBillingRequestTest $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[1]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::listUserBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listUserBillings()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $self->request = (new FindUserBillingRequest())->replace($self->input());
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_filterParams(): void
    {
        $this->should('return an array of specified filter params', function (): void {
            $this->assertEquals($this->filterParams(), $this->request->filterParams());
        });
        $this->should('return an array of allowed filter params only', function (): void {
            $request = (new FindUserBillingRequest())->replace(['name' => '太郎'] + $this->input());
            $this->assertEquals($this->filterParams(), $request->filterParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_paginationParams(): void
    {
        $this->should('return an array of pagination params', function (): void {
            $this->assertSame(self::PAGINATION_PARAMS, $this->request->paginationParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->input());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when providedIn is invalid date' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2021/10'],
                ['providedIn' => '2021-10'],
            ],
            'when issuedIn is invalid date' => [
                ['issuedIn' => ['正しい日付を入力してください。']],
                ['issuedIn' => '2021/10'],
                ['issuedIn' => '2021-10'],
            ],
            'when isTransacted is not boolean_ext' => [
                ['isTransacted' => ['trueかfalseにしてください。']],
                ['isTransacted' => 'string'],
                ['isTransacted' => 'true'],
            ],
            'when isDeposited is not boolean_ext' => [
                ['isDeposited' => ['trueかfalseにしてください。']],
                ['isDeposited' => 'string'],
                ['isDeposited' => 'true'],
            ],
            'when result is not exist' => [
                ['result' => ['利用者請求：請求結果を指定してください。']],
                ['result' => self::INVALID_ENUM_VALUE],
                ['result' => UserBillingResult::paid()->value()],
            ],
            'when usedService is not exist' => [
                ['usedService' => ['利用者請求：利用サービスを指定してください。']],
                ['usedService' => self::INVALID_ENUM_VALUE],
                ['usedService' => UserBillingUsedService::disabilitiesWelfareService()->value()],
            ],
            'when paymentMethod is invalid' => [
                ['paymentMethod' => ['支払方法を指定してください。']],
                ['paymentMethod' => self::INVALID_ENUM_VALUE],
                ['paymentMethod' => PaymentMethod::withdrawal()->value()],
            ],
            'when userId is not exist' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->examples->users[0]->id],
            ],
            'when officeId is not exist' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->offices[0]->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->input());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->input());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力値.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            ...self::PAGINATION_PARAMS,
            ...$this->filterParams(),
            'providedIn' => '2020-04',
            'issuedIn' => '2020-05',
            'result' => (string)UserBillingResult::paid()->value(),
            'usedService' => (string)UserBillingUsedService::disabilitiesWelfareService()->value(),
            'paymentMethod' => (string)PaymentMethod::withdrawal()->value(),
        ];
    }

    /**
     * 検索項目の定義.
     *
     * @return array
     */
    private function filterParams()
    {
        return [
            'providedIn' => Carbon::parse('2020-04'),
            'issuedIn' => Carbon::parse('2020-05'),
            'isTransacted' => true,
            'isDeposited' => true,
            'result' => UserBillingResult::paid(),
            'usedService' => UserBillingUsedService::disabilitiesWelfareService(),
            'paymentMethod' => PaymentMethod::withdrawal(),
            'userId' => 1,
            'officeId' => 1,
        ];
    }
}
